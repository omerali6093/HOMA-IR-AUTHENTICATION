<?php

session_start();

require_once "../config/database.php";


if (!isset($_SESSION["doctor_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$doctor_id = $_SESSION["doctor_id"];

// Get patient ID from URL
$patient_id = isset($_GET["patient_id"])
    ? (int) $_GET["patient_id"]
    : 0;

$patient = null;
$error = "";
$result = null;

if ($patient_id > 0) {

    $stmt = $conn->prepare("
        SELECT id, name, age, contact, city
        FROM patients
        WHERE id = ?
        AND doctor_id = ?
    ");

    $stmt->bind_param(
        "ii",
        $patient_id,
        $doctor_id
    );

    $stmt->execute();

    $query_result = $stmt->get_result();

    if ($query_result->num_rows === 1) {

        $patient = $query_result->fetch_assoc();

    } else {

        $error = "Patient not found.";
    }

    $stmt->close();
}


// Handle calculator submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $patient_id = (int) ($_POST["patient_id"] ?? 0);

    $glucose_unit = $_POST["glucose_unit"] ?? "mg_dl";

    $fasting_glucose = trim(
        $_POST["fasting_glucose"] ?? ""
    );

    $fasting_insulin = trim(
        $_POST["fasting_insulin"] ?? ""
    );


    // Validate patient
    if ($patient_id <= 0) {

        $error = "Invalid patient.";

    } else {

        // Make sure patient belongs to logged-in doctor
        $stmt = $conn->prepare("
            SELECT id, name, age, contact, city
            FROM patients
            WHERE id = ?
            AND doctor_id = ?
        ");

        $stmt->bind_param(
            "ii",
            $patient_id,
            $doctor_id
        );

        $stmt->execute();

        $query_result = $stmt->get_result();

        if ($query_result->num_rows !== 1) {

            $error = "Patient not found.";

        } else {

            $patient = $query_result->fetch_assoc();
        }

        $stmt->close();
    }


    // Validate calculator inputs
    if ($error === "") {

        if (
            $fasting_glucose === "" ||
            $fasting_insulin === ""
        ) {

            $error = "Please enter fasting glucose and fasting insulin.";

        } elseif (
            !is_numeric($fasting_glucose) ||
            !is_numeric($fasting_insulin)
        ) {

            $error = "Please enter valid numeric values.";

        } else {

            $fasting_glucose = (float) $fasting_glucose;
            $fasting_insulin = (float) $fasting_insulin;


            if ($fasting_glucose <= 0) {

                $error = "Fasting glucose must be greater than 0.";

            } elseif ($fasting_insulin <= 0) {

                $error = "Fasting insulin must be greater than 0.";

            } elseif (
                $glucose_unit !== "mg_dl" &&
                $glucose_unit !== "mmol_l"
            ) {

                $error = "Invalid glucose unit.";

            } else {

                // HOMA-IR calculation
                if ($glucose_unit === "mg_dl") {

                    $homa_ir =
                        ($fasting_glucose * $fasting_insulin) / 405;

                } else {

                    $homa_ir =
                        ($fasting_glucose * $fasting_insulin) / 22.5;
                }


                $result = round($homa_ir, 2);
            }
        }
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

    <title>HOMA-IR Calculator</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

</head>

<body>

<div class="calculator-container">

    <div class="calculator-header">

        <div>
            <h1>HOMA-IR Calculator</h1>

            <p>
                Welcome,
                <?php echo htmlspecialchars($_SESSION["doctor_name"]); ?>
            </p>
        </div>

        <a
            href="../logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>


    <?php if ($error !== ""): ?>

        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <?php if ($patient): ?>

        <div class="patient-card">

            <h2>Patient Information</h2>

            <div class="patient-info">

                <div>
                    <strong>Name</strong>
                    <span>
                        <?php echo htmlspecialchars($patient["name"]); ?>
                    </span>
                </div>

                <div>
                    <strong>Age</strong>
                    <span>
                        <?php echo htmlspecialchars($patient["age"]); ?>
                    </span>
                </div>

                <div>
                    <strong>Contact</strong>
                    <span>
                        <?php echo htmlspecialchars($patient["contact"]); ?>
                    </span>
                </div>

                <div>
                    <strong>City</strong>
                    <span>
                        <?php echo htmlspecialchars($patient["city"]); ?>
                    </span>
                </div>

            </div>

        </div>


        <div class="calculator-card">

            <h2>Enter Fasting Values</h2>

            <p class="calculator-description">
                Enter the patient's fasting laboratory values.
            </p>


            <form method="POST">

                <input
                    type="hidden"
                    name="patient_id"
                    value="<?php echo $patient["id"]; ?>"
                >


                <div class="form-group">

                    <label for="glucose_unit">
                        Glucose Unit
                    </label>

                    <select
                        id="glucose_unit"
                        name="glucose_unit"
                        required
                    >

                        <option
                            value="mg_dl"
                            <?php
                            echo (
                                ($_POST["glucose_unit"] ?? "mg_dl")
                                === "mg_dl"
                            )
                                ? "selected"
                                : "";
                            ?>
                        >
                            mg/dL
                        </option>

                        <option
                            value="mmol_l"
                            <?php
                            echo (
                                ($_POST["glucose_unit"] ?? "")
                                === "mmol_l"
                            )
                                ? "selected"
                                : "";
                            ?>
                        >
                            mmol/L
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label for="fasting_glucose">
                        Fasting Glucose
                    </label>

                    <input
                        type="number"
                        id="fasting_glucose"
                        name="fasting_glucose"
                        step="0.01"
                        min="0.01"
                        placeholder="Enter fasting glucose"
                        value="<?php
                            echo htmlspecialchars(
                                $_POST["fasting_glucose"] ?? ""
                            );
                        ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="fasting_insulin">
                        Fasting Insulin
                    </label>

                    <input
                        type="number"
                        id="fasting_insulin"
                        name="fasting_insulin"
                        step="0.01"
                        min="0.01"
                        placeholder="Enter fasting insulin"
                        value="<?php
                            echo htmlspecialchars(
                                $_POST["fasting_insulin"] ?? ""
                            );
                        ?>"
                        required
                    >

                    <small>
                        Enter insulin in µIU/mL (mU/L).
                    </small>

                </div>


                <button
                    type="submit"
                    class="btn-primary"
                >
                    Calculate HOMA-IR
                </button>

            </form>

        </div>


        <?php if ($result !== null): ?>

            <div class="result-card">

                <p class="result-label">
                    HOMA-IR Result
                </p>

                <div class="result-value">
                    <?php echo htmlspecialchars($result); ?>
                </div>

                <p class="result-note">
                    HOMA-IR is a surrogate measure of insulin resistance.
                    Interpretation should be based on the patient's clinical
                    context and the laboratory's reference information.
                </p>

            </div>

        <?php endif; ?>


    <?php else: ?>

        <div class="calculator-card">

            <h2>No Patient Selected</h2>

            <p>
                Please add a patient before starting a calculation.
            </p>

            <a
                href="../patients/add.php"
                class="btn-primary"
            >
                Add Patient
            </a>

        </div>

    <?php endif; ?>

</div>

</body>

</html>