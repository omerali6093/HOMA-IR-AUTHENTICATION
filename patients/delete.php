<?php

session_start();

require_once "../config/database.php";


if (!isset($_SESSION["doctor_id"])) {

    header("Location: ../auth/login.php");
    exit;
}


$doctor_id = $_SESSION["doctor_id"];

$patient_id = (int) ($_GET["id"] ?? 0);


if ($patient_id > 0) {

    $stmt = $conn->prepare(
        "DELETE FROM patients
         WHERE id = ?
         AND doctor_id = ?"
    );


    $stmt->bind_param(
        "ii",
        $patient_id,
        $doctor_id
    );


    $stmt->execute();

    $stmt->close();
}


header("Location: list.php");

exit;