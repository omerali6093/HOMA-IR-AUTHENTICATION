<?php

session_start();

require_once "../config/database.php";
require_once "../includes/ui.php";

// If already logged in, go directly to calculator
if (isset($_SESSION["doctor_id"])) {
    header("Location: ../calculator/index.php");
    exit;
}

$error = "";
$success = "";

// Message left by the Google sign-up flow (google-login.php / google-callback.php)
if (isset($_SESSION["signup_error"])) {

    $error = $_SESSION["signup_error"];

    unset($_SESSION["signup_error"]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // Validation
    if ($name === "" || $email === "" || $password === "" || $confirm_password === "") {

        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } elseif (!isset($_POST["terms"])) {

        $error = "Please agree to the Terms & Conditions and Privacy Policy to continue.";

    } else {

        // Check if email already exists
        $stmt = $conn->prepare(
            "SELECT id FROM doctors WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $error = "An account with this email already exists.";

        } else {

            // Hash password
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Create doctor account
            $stmt = $conn->prepare(
                "INSERT INTO doctors (name, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $hashed_password
            );

            if ($stmt->execute()) {

                // Get newly created doctor ID
                $doctor_id = $stmt->insert_id;

                // Create secure session
                session_regenerate_id(true);

                $_SESSION["doctor_id"] = $doctor_id;
                $_SESSION["doctor_name"] = $name;
                $_SESSION["doctor_email"] = $email;

                // After signup → patient
                header("Location: ../patients/add.php");
                exit;

            } else {

                $error = "Something went wrong. Please try again.";
            }
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Doctor Signup - HOMA-IR</title>

    <link
        rel="stylesheet"
        href="../assets/homa-ui.css"
    >

</head>

<body class="hu-body">

<div class="hu-page">

    <?php hu_page_header(1); ?>

    <main class="hu-main">

        <section class="hu-card">

            <div class="hu-card__head">

                <div class="hu-card__badge">
                    <?php echo hu_icon("user-solid"); ?>
                </div>

                <div>

                    <h2 class="hu-card__title">
                        Create Your Account
                    </h2>

                    <p class="hu-card__sub">
                        Register to Access the HOMA-IR Calculator
                        and save your results.
                    </p>

                </div>

            </div>


            <div
                class="hu-alert"
                id="form-alert"
                role="alert"
                <?php echo $error === "" ? "hidden" : ""; ?>
            >
                <?php echo hu_icon("alert"); ?>

                <span id="form-alert-text"><?php echo htmlspecialchars($error); ?></span>
            </div>


            <form method="POST" id="signup-form">

                <div class="hu-field">

                    <label class="hu-label" for="name">
                        Full Name <span class="hu-req" aria-hidden="true">*</span>
                    </label>

                    <div class="hu-input">

                        <?php echo hu_icon("user", "hu-input__icon"); ?>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter your full name"
                            autocomplete="name"
                            value="<?php echo htmlspecialchars($_POST["name"] ?? ""); ?>"
                            required
                        >

                    </div>

                </div>


                <div class="hu-field">

                    <label class="hu-label" for="email">
                        Email Address <span class="hu-req" aria-hidden="true">*</span>
                    </label>

                    <div class="hu-input">

                        <?php echo hu_icon("mail", "hu-input__icon"); ?>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email address"
                            autocomplete="email"
                            value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"
                            required
                        >

                    </div>

                </div>


                <div class="hu-field">

                    <label class="hu-label" for="password">
                        Password <span class="hu-req" aria-hidden="true">*</span>
                    </label>

                    <div class="hu-input">

                        <?php echo hu_icon("lock", "hu-input__icon"); ?>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Create a password"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="hu-input__toggle"
                            data-toggle="password"
                            aria-label="Show password"
                            aria-pressed="false"
                        >
                            <?php echo hu_icon("eye", "icon-on"); ?>
                            <?php echo hu_icon("eye-off", "icon-off"); ?>
                        </button>

                    </div>

                </div>


                <div class="hu-field">

                    <label class="hu-label" for="confirm_password">
                        Confirm Password <span class="hu-req" aria-hidden="true">*</span>
                    </label>

                    <div class="hu-input">

                        <?php echo hu_icon("lock", "hu-input__icon"); ?>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Confirm your password"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="hu-input__toggle"
                            data-toggle="confirm_password"
                            aria-label="Show password"
                            aria-pressed="false"
                        >
                            <?php echo hu_icon("eye", "icon-on"); ?>
                            <?php echo hu_icon("eye-off", "icon-off"); ?>
                        </button>

                    </div>

                </div>


                <!-- Terms & Conditions -->

                <label class="hu-check" id="terms-label">

                    <input
                        type="checkbox"
                        id="terms"
                        name="terms"
                        value="1"
                        <?php echo isset($_POST["terms"]) ? "checked" : ""; ?>
                        required
                    >

                    <span class="hu-check__box">
                        <?php echo hu_icon("check"); ?>
                    </span>

                    <span>
                        I agree to the
                        <a href="terms.php" target="_blank" rel="noopener">Terms &amp; Conditions</a>
                        and
                        <a href="privacy.php" target="_blank" rel="noopener">Privacy Policy</a>
                    </span>

                </label>


                <button
                    type="submit"
                    class="hu-btn hu-btn--primary"
                >
                    Create Account
                    <?php echo hu_icon("arrow-right"); ?>
                </button>


                <div class="hu-or">OR</div>


                <!-- Sign up with Google (posts the same form so the terms box is checked on the server too) -->

                <button
                    type="submit"
                    class="hu-btn hu-btn--google"
                    id="google-btn"
                    formaction="google-login.php"
                    formnovalidate
                >
                    <?php echo hu_google_logo(); ?>
                    Sign up with Google
                </button>

            </form>


            <p class="hu-switch">

                Already have an account?

                <a href="login.php">
                    Sign In
                </a>

            </p>

        </section>

    </main>

</div>


<script>
(function () {

    // Show / hide password
    document.querySelectorAll("[data-toggle]").forEach(function (btn) {

        btn.addEventListener("click", function () {

            var input   = document.getElementById(btn.getAttribute("data-toggle"));
            var showing = input.type === "text";

            input.type = showing ? "password" : "text";

            btn.setAttribute("aria-pressed", showing ? "false" : "true");
            btn.setAttribute("aria-label", showing ? "Show password" : "Hide password");
        });
    });


    // "Sign up with Google" needs the Terms box ticked first
    var terms      = document.getElementById("terms");
    var termsLabel = document.getElementById("terms-label");
    var alertBox   = document.getElementById("form-alert");
    var alertText  = document.getElementById("form-alert-text");

    document.getElementById("google-btn").addEventListener("click", function (event) {

        if (terms.checked) {
            return;
        }

        event.preventDefault();

        alertText.textContent =
            "Please agree to the Terms & Conditions and Privacy Policy to continue.";

        alertBox.hidden = false;
        termsLabel.classList.add("is-invalid");

        terms.focus();
    });

    terms.addEventListener("change", function () {

        if (terms.checked) {
            termsLabel.classList.remove("is-invalid");
        }
    });

})();
</script>

</body>

</html>
