<?php

session_start();

if (!isset($_SESSION["doctor_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$patient_id = $_GET["patient_id"] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOMA-IR Calculator</title>
</head>
<body>

    <h1>HOMA-IR Calculator</h1>

    <?php if ($patient_id): ?>
        <p>Patient ID: <?php echo htmlspecialchars($patient_id); ?></p>
    <?php else: ?>
        <p>No patient selected.</p>
    <?php endif; ?>

    <a href="../logout.php">Logout</a>

</body>
</html>