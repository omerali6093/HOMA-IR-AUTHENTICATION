<?php

/**
 * Google sign-up settings (OAuth 2.0).
 *
 * Do NOT put the real client secret in this file - it is committed to git.
 * Provide the values in ONE of these ways instead:
 *
 *   1. Environment variables:  GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET
 *      (optional: GOOGLE_REDIRECT_URI)
 *
 *   2. A local file  config/google.local.php  (already git-ignored) that
 *      returns an array, e.g.
 *
 *          <?php
 *          return [
 *              "client_id"     => "1234-abc.apps.googleusercontent.com",
 *              "client_secret" => "GOCSPX-xxxxxxxx",
 *          ];
 *
 * See README.md ("Google sign-up setup") for the Google Cloud Console steps.
 */

$settings = [

    "client_id"     => getenv("GOOGLE_CLIENT_ID")     ?: "",
    "client_secret" => getenv("GOOGLE_CLIENT_SECRET") ?: "",

    // Leave empty to auto-detect from the current request:
    //   <scheme>://<host>/<project folder>/auth/google-callback.php
    // This exact URL must be listed under "Authorized redirect URIs".
    "redirect_uri"  => getenv("GOOGLE_REDIRECT_URI")  ?: "",

    // Google's server-to-server endpoints. Only change these for testing
    // or when going through a proxy.
    "token_url"     => getenv("GOOGLE_TOKEN_URL")    ?: "https://oauth2.googleapis.com/token",
    "userinfo_url"  => getenv("GOOGLE_USERINFO_URL") ?: "https://openidconnect.googleapis.com/v1/userinfo",
];

$local = __DIR__ . "/google.local.php";

if (is_file($local)) {
    $settings = array_merge($settings, (array) require $local);
}

return $settings;
