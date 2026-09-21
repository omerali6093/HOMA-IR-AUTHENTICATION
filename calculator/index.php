<?php

session_start();

require_once "../config/database.php";

// Check doctor login
if (!isset($_SESSION["doctor_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$doctor_id = $_SESSION["doctor_id"];

// Get patient ID
$patient_id = isset($_GET["patient_id"])
    ? (int) $_GET["patient_id"]
    : 0;

$patient = null;
$error = "";
$result = null;

// --------------------------------------------------
// GET PATIENT
// --------------------------------------------------

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

        $error = "Patient not found or you do not have access to this patient.";
    }

    $stmt->close();

} else {

    $error = "Please select a patient first.";
}


// --------------------------------------------------
// CALCULATOR
// --------------------------------------------------

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && $patient !== null
) {

    $glucose_unit = $_POST["glucose_unit"] ?? "";
    $fasting_glucose = $_POST["fasting_glucose"] ?? "";
    $fasting_insulin = $_POST["fasting_insulin"] ?? "";


    // Validation

    if (
        $glucose_unit === ""
        || $fasting_glucose === ""
        || $fasting_insulin === ""
    ) {

        $error = "Please fill in all calculator fields.";

    } elseif (
        !is_numeric($fasting_glucose)
        || !is_numeric($fasting_insulin)
    ) {

        $error = "Please enter valid numeric values.";

    } elseif (
        $fasting_glucose <= 0
        || $fasting_insulin <= 0
    ) {

        $error = "Values must be greater than zero.";

    } elseif (
        $glucose_unit !== "mg_dl"
        && $glucose_unit !== "mmol_l"
    ) {

        $error = "Invalid glucose unit.";

    } else {

        $fasting_glucose = (float) $fasting_glucose;
        $fasting_insulin = (float) $fasting_insulin;


        // Calculate HOMA-IR

        if ($glucose_unit === "mg_dl") {

            $homa_ir =
                ($fasting_glucose * $fasting_insulin) / 405;

        } else {

            $homa_ir =
                ($fasting_glucose * $fasting_insulin) / 22.5;
        }


        $result = round($homa_ir, 2);


        // --------------------------------------------------
        // SAVE RESULT TO DATABASE
        // --------------------------------------------------

        $stmt = $conn->prepare("
            INSERT INTO homa_results
            (
                doctor_id,
                patient_id,
                fasting_glucose,
                glucose_unit,
                fasting_insulin,
                homa_ir
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iidsdd",
            $doctor_id,
            $patient_id,
            $fasting_glucose,
            $glucose_unit,
            $fasting_insulin,
            $result
        );

        if (!$stmt->execute()) {

            $error = "Calculation completed but could not be saved.";
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

    <title>HOMA-IR Calculator</title>

    <link
        rel="stylesheet"
        href="../assets/style.css"
    >

    <style>

/* ==================================================
   CALCULATOR PAGE
================================================== */

:root {
    --primary-blue: #2563eb;
    --dark-blue: #1e40af;
    --light-blue: #eff6ff;
    --blue-border: #bfdbfe;

    --text-dark: #172033;
    --text-gray: #64748b;

    --white: #ffffff;
    --background: #f5f9ff;

    --border: #e2e8f0;

    --success: #16a34a;
    --success-light: #f0fdf4;

    --danger: #dc2626;
    --danger-light: #fef2f2;
}


/* Reset */

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family:
        Inter,
        Arial,
        Helvetica,
        sans-serif;

    background: var(--background);
    color: var(--text-dark);
}


/* ==================================================
   NAVBAR
================================================== */

.calculator-navbar {
    height: 72px;

    background: var(--white);

    border-bottom: 1px solid var(--border);

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 7%;

    position: sticky;
    top: 0;
    z-index: 100;
}


.brand {
    display: flex;
    align-items: center;
    gap: 12px;

    font-size: 20px;
    font-weight: 800;

    color: var(--dark-blue);
}


.brand-icon {
    width: 38px;
    height: 38px;

    background: var(--primary-blue);

    color: white;

    border-radius: 10px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 18px;
}


.nav-right {
    display: flex;
    align-items: center;
    gap: 18px;
}


.doctor-name {
    color: var(--text-gray);
    font-size: 14px;
}


.logout-btn {
    text-decoration: none;

    color: var(--danger);

    border: 1px solid #fecaca;

    background: white;

    padding: 9px 16px;

    border-radius: 8px;

    font-size: 14px;
    font-weight: 600;

    transition: 0.2s;
}


.logout-btn:hover {
    background: var(--danger-light);
}


/* ==================================================
   MAIN
================================================== */

.calculator-wrapper {
    width: min(1100px, 92%);

    margin: 45px auto 70px;
}


/* Header */

.page-header {
    margin-bottom: 30px;
}


.page-header h1 {
    margin: 0 0 8px;

    font-size: 32px;

    color: var(--text-dark);
}


.page-header p {
    margin: 0;

    color: var(--text-gray);

    font-size: 15px;
}


/* ==================================================
   PATIENT CARD
================================================== */

.patient-card {
    background: white;

    border: 1px solid var(--blue-border);

    border-radius: 16px;

    padding: 24px;

    margin-bottom: 24px;

    box-shadow:
        0 8px 30px rgba(37, 99, 235, 0.06);
}


.patient-header {
    display: flex;

    align-items: center;

    gap: 14px;

    margin-bottom: 20px;
}


.patient-avatar {
    width: 48px;
    height: 48px;

    border-radius: 12px;

    background: var(--light-blue);

    color: var(--primary-blue);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 21px;

    font-weight: 800;
}


.patient-header h2 {
    margin: 0;

    font-size: 18px;
}


.patient-header span {
    display: block;

    margin-top: 3px;

    color: var(--text-gray);

    font-size: 13px;
}


.patient-details {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;
}


.patient-detail {
    background: #f8fafc;

    border: 1px solid #e8eef6;

    padding: 14px 16px;

    border-radius: 10px;
}


.patient-detail-label {
    display: block;

    color: var(--text-gray);

    font-size: 12px;

    margin-bottom: 5px;
}


.patient-detail-value {
    font-size: 14px;

    font-weight: 600;

    color: var(--text-dark);
}


/* ==================================================
   CALCULATOR GRID
================================================== */

.calculator-grid {
    display: grid;

    grid-template-columns:
        1fr 0.8fr;

    gap: 24px;

    align-items: stretch;
}


/* ==================================================
   CALCULATOR CARD
================================================== */

.calculator-card {
    background: white;

    border-radius: 16px;

    border: 1px solid var(--border);

    padding: 30px;

    box-shadow:
        0 10px 35px rgba(15, 23, 42, 0.05);
}


.calculator-card-header {
    margin-bottom: 25px;
}


.calculator-card-header h2 {
    margin: 0 0 7px;

    font-size: 21px;
}


.calculator-card-header p {
    margin: 0;

    color: var(--text-gray);

    font-size: 13px;
}


/* ==================================================
   FORM
================================================== */

.form-group {
    margin-bottom: 20px;
}


.form-group label {
    display: block;

    margin-bottom: 8px;

    font-size: 14px;

    font-weight: 600;

    color: var(--text-dark);
}


.input-wrapper {
    position: relative;
}


.input-wrapper input,
.form-select {
    width: 100%;

    height: 48px;

    padding: 0 14px;

    border: 1px solid #d7dee9;

    border-radius: 9px;

    background: white;

    color: var(--text-dark);

    font-size: 14px;

    outline: none;

    transition: 0.2s;
}


.input-wrapper input:focus,
.form-select:focus {
    border-color: var(--primary-blue);

    box-shadow:
        0 0 0 3px rgba(37, 99, 235, 0.10);
}


.unit-help {
    margin-top: 7px;

    color: var(--text-gray);

    font-size: 12px;
}


/* Calculate button */

.calculate-btn {
    width: 100%;

    height: 50px;

    border: none;

    border-radius: 9px;

    background: var(--primary-blue);

    color: white;

    font-size: 15px;

    font-weight: 700;

    cursor: pointer;

    transition: 0.2s;
}


.calculate-btn:hover {
    background: var(--dark-blue);

    transform: translateY(-1px);

    box-shadow:
        0 8px 20px rgba(37, 99, 235, 0.20);
}


/* ==================================================
   RESULT CARD
================================================== */

.result-card {
    background:
        linear-gradient(
            145deg,
            #eff6ff,
            #ffffff
        );

    border: 1px solid var(--blue-border);

    border-radius: 16px;

    padding: 30px;

    display: flex;

    flex-direction: column;

    justify-content: center;

    align-items: center;

    text-align: center;
}


.result-icon {
    width: 58px;
    height: 58px;

    border-radius: 16px;

    background: var(--primary-blue);

    color: white;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 25px;

    margin-bottom: 18px;
}


.result-card h3 {
    margin: 0;

    font-size: 18px;
}


.result-label {
    margin-top: 6px;

    color: var(--text-gray);

    font-size: 13px;
}


.result-number {
    margin: 18px 0 4px;

    font-size: 52px;

    line-height: 1;

    font-weight: 800;

    color: var(--primary-blue);
}


.result-unit {
    color: var(--text-gray);

    font-size: 13px;
}


.result-note {
    margin-top: 22px;

    padding: 11px 15px;

    background: white;

    border-radius: 8px;

    border: 1px solid var(--blue-border);

    color: var(--text-gray);

    font-size: 12px;

    line-height: 1.5;
}


/* ==================================================
   EMPTY RESULT
================================================== */

.empty-result {
    text-align: center;

    color: var(--text-gray);
}


.empty-result .result-icon {
    background: var(--light-blue);

    color: var(--primary-blue);
}


.empty-result p {
    max-width: 230px;

    font-size: 13px;

    line-height: 1.6;

    margin: 10px auto 0;
}


/* ==================================================
   ERROR
================================================== */

.calculator-error {
    background: var(--danger-light);

    border: 1px solid #fecaca;

    color: var(--danger);

    padding: 13px 16px;

    border-radius: 9px;

    margin-bottom: 20px;

    font-size: 14px;
}


/* ==================================================
   FOOTER
================================================== */

.calculator-footer {
    text-align: center;

    margin-top: 35px;

    color: #94a3b8;

    font-size: 12px;
}


/* ==================================================
   RESPONSIVE
================================================== */

@media (max-width: 800px) {

    .calculator-navbar {
        padding: 0 4%;
    }

    .doctor-name {
        display: none;
    }

    .calculator-wrapper {
        width: 92%;

        margin-top: 30px;
    }

    .calculator-grid {
        grid-template-columns: 1fr;
    }

    .patient-details {
        grid-template-columns: 1fr;
    }

    .page-header h1 {
        font-size: 27px;
    }
}


@media (max-width: 500px) {

    .calculator-navbar {
        height: 64px;
    }

    .brand {
        font-size: 17px;
    }

    .brand-icon {
        width: 34px;
        height: 34px;
    }

    .logout-btn {
        padding: 8px 11px;
    }

    .calculator-card,
    .result-card,
    .patient-card {
        padding: 20px;
    }

    .result-number {
        font-size: 44px;
    }
}

    </style>

</head>


<body>


<!-- ==================================================
     NAVBAR
================================================== -->

<nav class="calculator-navbar">

    <div class="brand">

        <div class="brand-icon">
            H
        </div>

        HOMA-IR

    </div>


    <div class="nav-right">

        <span class="doctor-name">

            Dr.
            <?php
            echo htmlspecialchars(
                $_SESSION["doctor_name"]
            );
            ?>

        </span>


        <a
            href="../logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

</nav>


<!-- ==================================================
     MAIN
================================================== -->

<main class="calculator-wrapper">


    <div class="page-header">

        <h1>
            HOMA-IR Calculator
        </h1>

        <p>
            Calculate and save the patient's HOMA-IR result.
        </p>

    </div>


    <?php if ($error !== ""): ?>

        <div class="calculator-error">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <?php if ($patient !== null): ?>


        <!-- PATIENT -->

        <section class="patient-card">

            <div class="patient-header">

                <div class="patient-avatar">

                    <?php
                    echo strtoupper(
                        substr($patient["name"], 0, 1)
                    );
                    ?>

                </div>


                <div>

                    <h2>
                        <?php
                        echo htmlspecialchars(
                            $patient["name"]
                        );
                        ?>
                    </h2>

                    <span>
                        Patient Information
                    </span>

                </div>

            </div>


            <div class="patient-details">


                <div class="patient-detail">

                    <span class="patient-detail-label">
                        Age
                    </span>

                    <span class="patient-detail-value">
                        <?php
                        echo htmlspecialchars(
                            $patient["age"]
                        );
                        ?> years
                    </span>

                </div>


                <div class="patient-detail">

                    <span class="patient-detail-label">
                        Contact
                    </span>

                    <span class="patient-detail-value">
                        <?php
                        echo htmlspecialchars(
                            $patient["contact"]
                        );
                        ?>
                    </span>

                </div>


                <div class="patient-detail">

                    <span class="patient-detail-label">
                        City
                    </span>

                    <span class="patient-detail-value">
                        <?php
                        echo htmlspecialchars(
                            $patient["city"]
                        );
                        ?>
                    </span>

                </div>


            </div>

        </section>



        <!-- CALCULATOR -->

        <div class="calculator-grid">


            <!-- FORM -->

            <section class="calculator-card">


                <div class="calculator-card-header">

                    <h2>
                        Enter Lab Values
                    </h2>

                    <p>
                        Enter the patient's fasting glucose
                        and fasting insulin values.
                    </p>

                </div>


                <form method="POST">


                    <!-- Glucose Unit -->

                    <div class="form-group">

                        <label for="glucose_unit">
                            Glucose Unit
                        </label>

                        <select
                            name="glucose_unit"
                            id="glucose_unit"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select unit
                            </option>

                            <option
                                value="mg_dl"
                                <?php
                                echo (
                                    ($_POST["glucose_unit"] ?? "")
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


                    <!-- Fasting Glucose -->

                    <div class="form-group">

                        <label for="fasting_glucose">
                            Fasting Glucose
                        </label>

                        <div class="input-wrapper">

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="fasting_glucose"
                                id="fasting_glucose"
                                placeholder="Enter fasting glucose"
                                value="<?php
                                echo htmlspecialchars(
                                    $_POST["fasting_glucose"] ?? ""
                                );
                                ?>"
                                required
                            >

                        </div>

                        <div class="unit-help">
                            Enter the value according to the selected unit.
                        </div>

                    </div>


                    <!-- Fasting Insulin -->

                    <div class="form-group">

                        <label for="fasting_insulin">
                            Fasting Insulin
                        </label>

                        <div class="input-wrapper">

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="fasting_insulin"
                                id="fasting_insulin"
                                placeholder="Enter fasting insulin"
                                value="<?php
                                echo htmlspecialchars(
                                    $_POST["fasting_insulin"] ?? ""
                                );
                                ?>"
                                required
                            >

                        </div>

                        <div class="unit-help">
                            Enter fasting insulin in µIU/mL.
                        </div>

                    </div>


                    <button
                        type="submit"
                        class="calculate-btn"
                    >
                        Calculate HOMA-IR
                    </button>


                </form>

            </section>



            <!-- RESULT -->

            <?php if ($result !== null): ?>

                <section class="result-card">

                    <div class="result-icon">
                        ✓
                    </div>

                    <h3>
                        Calculation Result
                    </h3>

                    <div class="result-label">
                        HOMA-IR Score
                    </div>

                    <div class="result-number">
                        <?php
                        echo htmlspecialchars(
                            $result
                        );
                        ?>
                    </div>

                    <div class="result-unit">
                        HOMA-IR
                    </div>

                    <div class="result-note">
                        Calculation saved successfully
                        to the database.
                    </div>

                </section>

            <?php else: ?>

                <section class="result-card empty-result">

                    <div class="result-icon">
                        +
                    </div>

                    <h3>
                        Your Result
                    </h3>

                    <p>
                        Enter the patient's laboratory
                        values and click
                        <strong>Calculate HOMA-IR</strong>
                        to see the result here.
                    </p>

                </section>

            <?php endif; ?>


        </div>


    <?php endif; ?>


    <div class="calculator-footer">

        HOMA-IR Calculator
        &nbsp;•&nbsp;
        Patient data is securely linked to the
        logged-in doctor.

    </div>


</main>


</body>

</html>