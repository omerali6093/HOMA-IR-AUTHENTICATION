<?php

session_start();

require_once "../config/database.php";


if (!isset($_SESSION["doctor_id"])) {

    header("Location: ../auth/login.php");
    exit;
}


$doctor_id = $_SESSION["doctor_id"];

$patient_id = (int) ($_GET["id"] ?? 0);


if ($patient_id <= 0) {

    header("Location: list.php");
    exit;
}


// ------------------------------------------------
// Get patient
// IMPORTANT: doctor_id is checked
// ------------------------------------------------

$stmt = $conn->prepare(
    "SELECT id, name, age, contact, city
     FROM patients
     WHERE id = ?
     AND doctor_id = ?"
);

$stmt->bind_param(
    "ii",
    $patient_id,
    $doctor_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    die("Patient not found.");
}


$patient = $result->fetch_assoc();

$error = "";


// ------------------------------------------------
// Update
// ------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $age = (int) ($_POST["age"] ?? 0);
    $contact = trim($_POST["contact"] ?? "");
    $city = trim($_POST["city"] ?? "");


    if (empty($name)) {

        $error = "Patient name is required.";

    } elseif ($age <= 0 || $age > 150) {

        $error = "Please enter a valid age.";

    } elseif (empty($contact)) {

        $error = "Contact number is required.";

    } elseif (empty($city)) {

        $error = "City is required.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE patients
             SET name = ?,
                 age = ?,
                 contact = ?,
                 city = ?
             WHERE id = ?
             AND doctor_id = ?"
        );


        $stmt->bind_param(
            "sissii",
            $name,
            $age,
            $contact,
            $city,
            $patient_id,
            $doctor_id
        );


        if ($stmt->execute()) {

            header("Location: list.php");
            exit;

        } else {

            $error = "Failed to update patient.";
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

    <title>Edit Patient</title>

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

                <h1>Edit Patient</h1>

                <p>
                    Update patient information
                </p>

            </div>

        </div>


        <?php if ($error): ?>

            <div class="error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-group">

                <label>Patient Name</label>

                <input
                    type="text"
                    name="name"
                    value="<?= htmlspecialchars($patient["name"]) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Age</label>

                <input
                    type="number"
                    name="age"
                    min="1"
                    max="150"
                    value="<?= htmlspecialchars($patient["age"]) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Contact Number</label>

                <input
                    type="text"
                    name="contact"
                    value="<?= htmlspecialchars($patient["contact"]) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>City</label>

                <input
                    type="text"
                    name="city"
                    value="<?= htmlspecialchars($patient["city"]) ?>"
                    required
                >

            </div>


            <button type="submit">
                Update Patient
            </button>

        </form>


        <p class="bottom-text">

            <a href="list.php">
                ← Back to Patients
            </a>

        </p>

    </div>

</div>

</body>

</html>