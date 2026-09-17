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


$doctor_id = $_SESSION["doctor_id"];


// ------------------------------------------------
// Get only this doctor's patients
// ------------------------------------------------

$stmt = $conn->prepare(
    "SELECT id, name, age, contact, city, created_at
     FROM patients
     WHERE doctor_id = ?
     ORDER BY id DESC"
);

$stmt->bind_param("i", $doctor_id);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Patients</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

</head>


<body>

<div class="page">

    <div class="card wide">

        <div class="page-header">

            <div>

                <h1>My Patients</h1>

                <p>
                    Manage your registered patients
                </p>

            </div>


            <div class="header-links">

                <a href="../dashboard/index.php">
                    Dashboard
                </a>

                <a href="add.php" class="small-button">
                    + Add Patient
                </a>

            </div>

        </div>


        <?php if ($result->num_rows === 0): ?>

            <div class="empty">

                <h3>No patients yet</h3>

                <p>
                    Add your first patient to get started.
                </p>

            </div>

        <?php else: ?>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>Name</th>

                            <th>Age</th>

                            <th>Contact</th>

                            <th>City</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php while ($patient = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($patient["name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($patient["age"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($patient["contact"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($patient["city"]) ?>
                            </td>

                            <td>

                                <a
                                    href="edit.php?id=<?= $patient["id"] ?>"
                                    class="edit-link"
                                >
                                    Edit
                                </a>


                                <a
                                    href="delete.php?id=<?= $patient["id"] ?>"
                                    class="delete-link"
                                    onclick="return confirm('Delete this patient?');"
                                >
                                    Delete
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>


    </div>

</div>

</body>

</html>