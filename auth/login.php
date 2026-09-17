<?php

session_start();

require_once "../config/database.php";

// If already logged in, go directly to calculator
if (isset($_SESSION["doctor_id"])) {
    header("Location: ../calculator/index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Validation
    if ($email === "" || $password === "") {

        $error = "Please enter your email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        // Find doctor by email
        $stmt = $conn->prepare(
            "SELECT id, name, email, password
             FROM doctors
             WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $doctor = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $doctor["password"])) {

                // Create new session ID
                session_regenerate_id(true);

                $_SESSION["doctor_id"] = $doctor["id"];
                $_SESSION["doctor_name"] = $doctor["name"];
                $_SESSION["doctor_email"] = $doctor["email"];

                // Login → Patient Form
                header("Location: ../patients/add.php");
                exit;

            } else {

                $error = "Incorrect email or password.";
            }

        } else {

            $error = "Incorrect email or password.";
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

    <title>Doctor Login - HOMA-IR</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

</head>

<body>

<div class="auth-container">

    <div class="auth-card">

        <h1>Doctor Login</h1>

        <p class="auth-subtitle">
            Login to access the HOMA-IR Calculator
        </p>

        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter email address"
                    value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter password"
                    required
                >

            </div>

            <button
                type="submit"
                class="btn-primary"
            >
                Login
            </button>

        </form>

        <p class="auth-footer">

            Don't have an account?

            <a href="signup.php">
                Create Account
            </a>

        </p>

    </div>

</div>

</body>

</html>