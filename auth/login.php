<?php

session_start();

require_once "../config/database.php";


if (isset($_SESSION["doctor_id"])) {

    header("Location: ../dashboard/index.php");
    exit;
}


$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";


    if (empty($email) || empty($password)) {

        $error = "Please enter email and password.";

    } else {

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


            if (
                password_verify(
                    $password,
                    $doctor["password"]
                )
            ) {

                session_regenerate_id(true);

           $_SESSION["doctor_id"] = $doctor["id"];
           $_SESSION["doctor_name"] = $doctor["name"];
           $_SESSION["doctor_email"] = $doctor["email"];

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

    <title>Doctor Login</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

</head>


<body>

<div class="auth-container">

    <div class="card">

        <h1>Doctor Login</h1>

        <p class="subtitle">
            Login to your account
        </p>


        <?php if ($error): ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

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


            <button type="submit">
                Login
            </button>

        </form>


        <p class="bottom-text">

            Don't have an account?

            <a href="signup.php">
                Create Account
            </a>

        </p>

    </div>

</div>

</body>

</html>