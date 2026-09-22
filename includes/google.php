<?php

/**
 * Helpers for "Sign up with Google" (OAuth 2.0 authorization-code flow + PKCE).
 *
 * Used by auth/google-login.php and auth/google-callback.php.
 *
 * Functions throw RuntimeException with a message that is safe to show to the
 * user. Technical details are written to the PHP error log instead.
 */


/**
 * Google settings from config/google.php.
 */
function google_config(): array
{
    static $config = null;

    if ($config === null) {
        $config = require __DIR__ . "/../config/google.php";
    }

    return $config;
}


/**
 * True once a real client id + secret have been provided.
 */
function google_is_configured(): bool
{
    $config = google_config();

    return trim((string) $config["client_id"]) !== ""
        && trim((string) $config["client_secret"]) !== "";
}


/**
 * The callback URL registered in Google Cloud Console.
 * Uses config "redirect_uri" if set, otherwise builds it from the request.
 */
function google_redirect_uri(): string
{
    $config = google_config();

    if (trim((string) $config["redirect_uri"]) !== "") {
        return trim($config["redirect_uri"]);
    }

    $https = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";
    $host  = $_SERVER["HTTP_HOST"] ?? "localhost";

    // Both google-login.php and google-callback.php live in /auth/, so the
    // folder of the running script is the folder of the callback.
    $folder = dirname($_SERVER["SCRIPT_NAME"] ?? "/auth/x.php");
    $folder = implode("/", array_map("rawurlencode", explode("/", $folder)));

    return ($https ? "https" : "http") . "://" . $host . $folder . "/google-callback.php";
}


/**
 * URL-safe base64 without padding (used for PKCE).
 */
function google_base64url(string $bytes): string
{
    return rtrim(strtr(base64_encode($bytes), "+/", "-_"), "=");
}


/**
 * Small cURL wrapper. Returns [http_status, decoded_json_or_null].
 */
function google_http(string $url, ?array $post = null, string $bearer = ""): array
{
    if (!function_exists("curl_init")) {
        error_log("[google-signup] PHP cURL extension is not enabled.");
        throw new RuntimeException("Google sign-up isn't available on this server right now.");
    }

    $ch = curl_init($url);

    $headers = ["Accept: application/json"];

    if ($bearer !== "") {
        $headers[] = "Authorization: Bearer " . $bearer;
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => $headers,
    ]);

    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    $body   = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($body === false) {
        // Common on XAMPP/Windows: "SSL certificate problem" -> set curl.cainfo in php.ini
        error_log("[google-signup] cURL error: " . curl_error($ch));
        curl_close($ch);
        throw new RuntimeException("We couldn't reach Google. Please try again.");
    }

    curl_close($ch);

    return [$status, json_decode($body, true)];
}


/**
 * Exchange the authorization code for the person's Google profile.
 *
 * @return array{email:string, name:string}
 */
function google_fetch_profile(string $code, string $codeVerifier): array
{
    $config = google_config();

    // 1) code -> access token
    [$status, $token] = google_http($config["token_url"], [
        "code"          => $code,
        "client_id"     => $config["client_id"],
        "client_secret" => $config["client_secret"],
        "redirect_uri"  => google_redirect_uri(),
        "grant_type"    => "authorization_code",
        "code_verifier" => $codeVerifier,
    ]);

    if ($status !== 200 || empty($token["access_token"])) {
        error_log("[google-signup] Token exchange failed (HTTP $status): " . json_encode($token));
        throw new RuntimeException("Google sign-up failed. Please try again.");
    }

    // 2) access token -> profile
    [$status, $info] = google_http($config["userinfo_url"], null, $token["access_token"]);

    if ($status !== 200 || !is_array($info) || empty($info["email"])) {
        error_log("[google-signup] Userinfo failed (HTTP $status): " . json_encode($info));
        throw new RuntimeException("Google sign-up failed. Please try again.");
    }

    // Only trust an email address that Google has verified.
    $verified = $info["email_verified"] ?? false;

    if ($verified !== true && $verified !== "true") {
        throw new RuntimeException("Your Google email address isn't verified. Please verify it with Google and try again.");
    }

    return [
        "email" => trim($info["email"]),
        "name"  => trim((string) ($info["name"] ?? "")),
    ];
}


/**
 * Find the doctor with this email, or create the account.
 *
 * New accounts get a random, unusable password hash (the login form can't be
 * used for them - they sign in with Google, or can reset later if you add that).
 *
 * @return array{id:int, name:string, email:string}
 */
function google_find_or_create_doctor(mysqli $conn, array $profile): array
{
    $email = $profile["email"];

    $stmt = $conn->prepare("SELECT id, name, email FROM doctors WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existing) {
        return [
            "id"    => (int) $existing["id"],
            "name"  => $existing["name"],
            "email" => $existing["email"],
        ];
    }

    $name = $profile["name"] !== "" ? $profile["name"] : (string) strstr($email, "@", true);

    if ($name === "") {
        $name = $email;
    }

    $hashed_password = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO doctors (name, email, password)
         VALUES (?, ?, ?)"
    );

    $stmt->bind_param("sss", $name, $email, $hashed_password);

    if (!$stmt->execute()) {
        error_log("[google-signup] Could not create doctor: " . $stmt->error);
        $stmt->close();
        throw new RuntimeException("Something went wrong. Please try again.");
    }

    $id = (int) $stmt->insert_id;
    $stmt->close();

    return ["id" => $id, "name" => $name, "email" => $email];
}
