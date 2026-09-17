<?php

session_start();


if (!isset($_SESSION["doctor_id"])) {

    header("Location: ../auth/login.php");
    exit;
}


$doctor_name = $_SESSION["doctor_name"];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Doctor Dashboard</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

</head>


<body>

<div class="dashboard">

    <div class="dashboard-header">

        <div>

            <h1>
                Doctor Dashboard
            </h1>

            <p>
                Welcome, Dr.
                <?= htmlspecialchars($doctor_name) ?>
            </p>

        </div>


        <a
            href="../logout.php"
            class="logout-button"
        >
            Logout
        </a>

    </div>


    <div class="dashboard-actions">

        <a
            href="../patients/add.php"
            class="dashboard-card"
        >

            <h2>Add Patient</h2>

            <p>
                Register a new patient
            </p>

        </a>


        <a
            href="../patients/list.php"
            class="dashboard-card"
        >

            <h2>My Patients</h2>

            <p>
                View and manage your patients
            </p>

        </a>

    </div>

</div>

</body>

</html>