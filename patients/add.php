<?php

session_start();

require_once "../config/database.php";


// ------------------------------------------------
// Protect page
// ------------------------------------------------

if (!isset($_SESSION["doctor_id"])) {

    header("Location: ../auth/login.php");
    exit;
}


// ------------------------------------------------
// Get logged-in doctor ID
// ------------------------------------------------

$doctor_id = $_SESSION["doctor_id"];

$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $age = (int) ($_POST["age"] ?? 0);
    $contact = trim($_POST["contact"] ?? "");
    $city = trim($_POST["city"] ?? "");


    // Validation

    if (empty($name)) {

        $error = "Patient name is required.";

    } elseif ($age <= 0 || $age > 150) {

        $error = "Please enter a valid age.";

    } elseif (empty($contact)) {

        $error = "Contact number is required.";

    } elseif (empty($city)) {

        $error = "City is required.";

    } else {


        // Insert patient

        $stmt = $conn->prepare(
            "INSERT INTO patients
            (doctor_id, name, age, contact, city)
            VALUES (?, ?, ?, ?, ?)"
        );


        $stmt->bind_param(
            "isiss",
            $doctor_id,
            $name,
            $age,
            $contact,
            $city
        );


        if ($stmt->execute()) {

            header("Location: list.php");
            exit;

        } else {

            $error = "Failed to add patient.";
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

    <title>Add Patient</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

</head>


<body>

<div class="page">

    <div class="card">

        <div class="page-header">

            <div>

                <h1>Add Patient</h1>

                <p>
                    Enter patient information
                </p>

            </div>

            <a href="../dashboard/index.php">
                Dashboard
            </a>

        </div>


        <?php if ($error): ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-group">

                <label>
                    Patient Name
                </label>

                <input
                    type="text"
                    name="name"
                    placeholder="Enter patient name"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Age
                </label>

                <input
                    type="number"
                    name="age"
                    min="1"
                    max="150"
                    placeholder="Enter age"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Contact Number
                </label>

                <input
                    type="text"
                    name="contact"
                    placeholder="03XX XXXXXXX"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    City
                </label>

                <input
                    type="text"
                    name="city"
                    placeholder="Enter city"
                    required
                >

            </div>


            <button type="submit">
                Add Patient
            </button>

        </form>

    </div>

</div>

</body>

</html>