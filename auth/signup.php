<?php

session_start();

require_once "../config/database.php";


// If already logged in
if (isset($_SESSION["doctor_id"])) {

    header("Location: ../dashboard/index.php");
    exit;
}


$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    // Validation

    if (
        empty($name) ||
        empty($email) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $error = "Please fill all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

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


            // Create doctor

            $stmt = $conn->prepare(
                "INSERT INTO doctors
                (name, email, password)
                VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $hashed_password
            );


            if ($stmt->execute()) {

                $doctor_id = $stmt->insert_id;


                // Create session

                session_regenerate_id(true);

                $_SESSION["doctor_id"] = $doctor_id;
                $_SESSION["doctor_name"] = $name;
                $_SESSION["doctor_email"] = $email;


                // Dashboard

                header(
                    "Location: ../dashboard/index.php"
                );

                exit;

            } else {

                $error = "Something went wrong.";
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

    <title>Doctor Signup</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

</head>


<body>

<div class="auth-container">

    <div class="card">

        <h1>Doctor Signup</h1>

        <p class="subtitle">
            Create your doctor account
        </p>


        <?php if ($error): ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-group">

                <label>Doctor Name</label>

                <input
                    type="text"
                    name="name"
                    placeholder="Dr. Ahmed"
                    required
                >

            </div>


            <div class="form-group">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    placeholder="doctor@example.com"
                    required
                >

            </div>


            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter password"
                    required
                >

            </div>


            <div class="form-group">

                <label>Confirm Password</label>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm password"
                    required
                >

            </div>


            <button type="submit">
                Create Account
            </button>

        </form>


        <p class="bottom-text">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </p>

    </div>

</div>

</body>

</html>