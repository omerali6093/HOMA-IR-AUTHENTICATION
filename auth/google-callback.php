<?php

session_start();

require_once "../config/database.php";
require_once "../includes/google.php";

// If already logged in, go directly to calculator
if (isset($_SESSION["doctor_id"])) {
    header("Location: ../calculator/index.php");
    exit;
}

/**
 * Back to the signup page with a message.
 */
function google_fail(string $message): void
{
    $_SESSION["signup_error"] = $message;

    header("Location: signup.php");
    exit;
}

// The one-time values saved by google-login.php
$flow = $_SESSION["google_oauth"] ?? null;

unset($_SESSION["google_oauth"]);

// Person closed the Google window / pressed "Cancel"
if (isset($_GET["error"])) {
    google_fail("Google sign-up was cancelled.");
}

$code  = (string) ($_GET["code"] ?? "");
$state = (string) ($_GET["state"] ?? "");

if (
    !is_array($flow)
    || $code === ""
    || !hash_equals((string) $flow["state"], $state)
    || time() - (int) $flow["time"] > 600
) {
    google_fail("Your Google sign-up session expired. Please try again.");
}

try {

    $profile = google_fetch_profile($code, $flow["verifier"]);
    $doctor  = google_find_or_create_doctor($conn, $profile);

} catch (RuntimeException $e) {

    google_fail($e->getMessage());

} catch (Throwable $e) {

    error_log("[google-signup] " . $e->getMessage());

    google_fail("Something went wrong. Please try again.");
}

// Create secure session (same values the normal login/signup store)
session_regenerate_id(true);

$_SESSION["doctor_id"]    = $doctor["id"];
$_SESSION["doctor_name"]  = $doctor["name"];
$_SESSION["doctor_email"] = $doctor["email"];

// After signup -> patient
header("Location: ../patients/add.php");
exit;
