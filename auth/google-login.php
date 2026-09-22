<?php

session_start();

require_once "../includes/google.php";

// If already logged in, go directly to calculator
if (isset($_SESSION["doctor_id"])) {
    header("Location: ../calculator/index.php");
    exit;
}

// Only reachable from the "Sign up with Google" button on the signup form
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: signup.php");
    exit;
}

// The Terms & Conditions box must be ticked before signing up with Google too
if (!isset($_POST["terms"])) {

    $_SESSION["signup_error"] =
        "Please agree to the Terms & Conditions and Privacy Policy to continue.";

    header("Location: signup.php");
    exit;
}

if (!google_is_configured()) {

    $message = "Google sign-up isn't set up yet.";

    // Local development: tell the developer exactly what to register in Google Cloud
    $host = $_SERVER["HTTP_HOST"] ?? "";

    if (strpos($host, "localhost") === 0 || strpos($host, "127.0.0.1") === 0) {
        $message .= " Add your Google client ID and secret (see README) and register this redirect URI: "
            . google_redirect_uri();
    }

    $_SESSION["signup_error"] = $message;

    header("Location: signup.php");
    exit;
}

$config = google_config();

// Anti-CSRF token + PKCE verifier, checked again in google-callback.php
$state    = bin2hex(random_bytes(16));
$verifier = google_base64url(random_bytes(32));

$_SESSION["google_oauth"] = [
    "state"    => $state,
    "verifier" => $verifier,
    "time"     => time(),
];

$query = http_build_query([
    "client_id"             => $config["client_id"],
    "redirect_uri"          => google_redirect_uri(),
    "response_type"         => "code",
    "scope"                 => "openid email profile",
    "state"                 => $state,
    "code_challenge"        => google_base64url(hash("sha256", $verifier, true)),
    "code_challenge_method" => "S256",
    "prompt"                => "select_account",
], "", "&", PHP_QUERY_RFC3986);

header("Location: https://accounts.google.com/o/oauth2/v2/auth?" . $query);
exit;
