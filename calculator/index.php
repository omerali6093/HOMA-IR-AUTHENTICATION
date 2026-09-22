<?php

session_start();

require_once "../config/database.php";
require_once "../includes/ui.php";

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

<?php

// Presentation only: which glucose-unit tab is selected (defaults to US units)
$selected_unit =
    (($_POST["glucose_unit"] ?? "") === "mmol_l")
    ? "mmol_l"
    : "mg_dl";

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
        href="../assets/homa-ui.css"
    >

</head>


<body class="hu-body">

<div class="hu-page">


    <!-- Logged-in doctor + logout -->

    <div class="hu-topbar">

        <span class="hu-user">

            <span class="hu-user__avatar">
                <?php
                echo htmlspecialchars(
                    strtoupper(substr($_SESSION["doctor_name"], 0, 1))
                );
                ?>
            </span>

            <?php
            echo htmlspecialchars(
                $_SESSION["doctor_name"]
            );
            ?>

        </span>

        <a
            href="../logout.php"
            class="hu-logout"
        >
            Logout
        </a>

    </div>


    <?php hu_page_header(2); ?>


    <main class="hu-main">


        <?php if ($error !== ""): ?>

            <div class="hu-alert" role="alert">

                <?php echo hu_icon("alert"); ?>

                <span>
                    <?php
                    echo htmlspecialchars($error);
                    ?>
                </span>

            </div>

        <?php endif; ?>


        <?php if ($patient !== null): ?>


            <!-- PATIENT -->

            <section class="hu-patient">

                <div class="hu-patient__who">

                    <div class="hu-patient__avatar">
                        <?php
                        echo htmlspecialchars(
                            strtoupper(substr($patient["name"], 0, 1))
                        );
                        ?>
                    </div>

                    <div>

                        <h2 class="hu-patient__name">
                            <?php
                            echo htmlspecialchars(
                                $patient["name"]
                            );
                            ?>
                        </h2>

                        <span class="hu-patient__role">
                            Patient Information
                        </span>

                    </div>

                </div>


                <div class="hu-patient__item">
                    Age
                    <b>
                        <?php
                        echo htmlspecialchars(
                            $patient["age"]
                        );
                        ?> years
                    </b>
                </div>


                <div class="hu-patient__item">
                    Contact
                    <b>
                        <?php
                        echo htmlspecialchars(
                            $patient["contact"]
                        );
                        ?>
                    </b>
                </div>


                <div class="hu-patient__item">
                    City
                    <b>
                        <?php
                        echo htmlspecialchars(
                            $patient["city"]
                        );
                        ?>
                    </b>
                </div>

            </section>


            <!-- CALCULATOR -->

            <section class="hu-card hu-card--calc">


                <div class="hu-card__head">

                    <div class="hu-card__badge hu-card__badge--solid">
                        <?php echo hu_icon("calc-solid"); ?>
                    </div>

                    <div>

                        <h2 class="hu-card__title">
                            HOMA-IR Calculator
                        </h2>

                        <p class="hu-card__sub">
                            Enter your fasting insulin and glucose values
                            to calculate your HOMA-IR score.
                        </p>

                    </div>

                    <?php echo hu_illustration(); ?>

                </div>


                <form method="POST">


                    <!-- Glucose unit -->

                    <div
                        class="hu-tabs"
                        role="radiogroup"
                        aria-label="Glucose unit"
                    >

                        <label class="hu-tab">

                            <input
                                type="radio"
                                name="glucose_unit"
                                value="mg_dl"
                                <?php echo $selected_unit === "mg_dl" ? "checked" : ""; ?>
                                required
                            >

                            <span>Glucose in mg/dL (US Units)</span>

                        </label>

                        <label class="hu-tab">

                            <input
                                type="radio"
                                name="glucose_unit"
                                value="mmol_l"
                                <?php echo $selected_unit === "mmol_l" ? "checked" : ""; ?>
                                required
                            >

                            <span>Glucose in mmol/L (International)</span>

                        </label>

                    </div>


                    <div class="hu-inputs">


                        <!-- Fasting Insulin -->

                        <div class="hu-metric">

                            <div class="hu-metric__head">

                                <?php echo hu_icon("tube", "hu-metric__icon"); ?>

                                <div>

                                    <label
                                        class="hu-metric__name"
                                        for="fasting_insulin"
                                    >
                                        Fasting Insulin
                                    </label>

                                    <span class="hu-metric__unit">
                                        (µIU/mL)
                                    </span>

                                </div>

                            </div>

                            <div class="hu-spin">

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="fasting_insulin"
                                    id="fasting_insulin"
                                    placeholder="Enter value"
                                    inputmode="decimal"
                                    data-step="1"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $_POST["fasting_insulin"] ?? ""
                                    );
                                    ?>"
                                    required
                                >

                                <span class="hu-spin__btns">

                                    <button
                                        type="button"
                                        data-dir="1"
                                        aria-label="Increase fasting insulin"
                                    >
                                        <?php echo hu_icon("chevron-up"); ?>
                                    </button>

                                    <button
                                        type="button"
                                        data-dir="-1"
                                        aria-label="Decrease fasting insulin"
                                    >
                                        <?php echo hu_icon("chevron-down"); ?>
                                    </button>

                                </span>

                            </div>

                            <p class="hu-metric__hint">
                                e.g. 5
                            </p>

                        </div>


                        <span class="hu-times" aria-hidden="true">&times;</span>


                        <!-- Fasting Glucose -->

                        <div class="hu-metric">

                            <div class="hu-metric__head">

                                <?php echo hu_icon("drop", "hu-metric__icon"); ?>

                                <div>

                                    <label
                                        class="hu-metric__name"
                                        for="fasting_glucose"
                                    >
                                        Fasting Glucose
                                    </label>

                                    <span
                                        class="hu-metric__unit"
                                        id="glucose-unit-label"
                                    >
                                        (mg/dL)
                                    </span>

                                </div>

                            </div>

                            <div class="hu-spin">

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="fasting_glucose"
                                    id="fasting_glucose"
                                    placeholder="Enter value"
                                    inputmode="decimal"
                                    data-step="1"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $_POST["fasting_glucose"] ?? ""
                                    );
                                    ?>"
                                    required
                                >

                                <span class="hu-spin__btns">

                                    <button
                                        type="button"
                                        data-dir="1"
                                        aria-label="Increase fasting glucose"
                                    >
                                        <?php echo hu_icon("chevron-up"); ?>
                                    </button>

                                    <button
                                        type="button"
                                        data-dir="-1"
                                        aria-label="Decrease fasting glucose"
                                    >
                                        <?php echo hu_icon("chevron-down"); ?>
                                    </button>

                                </span>

                            </div>

                            <p
                                class="hu-metric__hint"
                                id="glucose-hint"
                            >
                                e.g. 100
                            </p>

                        </div>


                    </div>


                    <button
                        type="submit"
                        class="hu-btn hu-btn--calc"
                    >
                        <?php echo hu_icon("calc"); ?>
                        Calculate HOMA-IR
                    </button>


                </form>


                <!-- RESULT -->

                <?php if ($result !== null): ?>

                    <div class="hu-result" id="result">

                        <h3 class="hu-result__title">
                            Your HOMA-IR Result
                        </h3>

                        <div class="hu-result__value">
                            <?php echo htmlspecialchars($result); ?>
                        </div>

                        <div class="hu-result__ok">

                            <?php echo hu_icon("check-circle"); ?>

                            Result calculated successfully

                        </div>

                    </div>


                    <div class="hu-meaning">

                        <div class="hu-meaning__head">

                            <?php echo hu_icon("bars"); ?>

                            <h3>What does this mean?</h3>

                        </div>

                        <table class="hu-table">

                            <tr>
                                <th scope="row">&lt; 1.0</th>
                                <td>Insulin-sensitive (Optimal)</td>
                            </tr>

                            <tr>
                                <th scope="row">1.0 – 1.9</th>
                                <td>May indicate early insulin resistance</td>
                            </tr>

                            <tr>
                                <th scope="row">2.0 – 2.8</th>
                                <td>Intermediate range</td>
                            </tr>

                            <tr>
                                <th scope="row">&ge; 2.9</th>
                                <td>May indicate significant insulin resistance</td>
                            </tr>

                        </table>

                    </div>


                    <p class="hu-footnote">

                        <?php echo hu_icon("info"); ?>

                        <span>
                            <b>Note:</b> Reference ranges may vary by population
                            and laboratory. Please consult your healthcare
                            provider for proper interpretation.
                        </span>

                    </p>

                <?php endif; ?>


            </section>


        <?php endif; ?>


    </main>

</div>


<script>
(function () {

    // Glucose unit tabs -> update the unit label + example under the glucose box
    var units = {
        mg_dl:  { label: "(mg/dL)",  hint: "e.g. 100", step: "1"   },
        mmol_l: { label: "(mmol/L)", hint: "e.g. 5.5", step: "0.1" }
    };

    var radios       = document.querySelectorAll('input[name="glucose_unit"]');
    var unitLabel    = document.getElementById("glucose-unit-label");
    var hint         = document.getElementById("glucose-hint");
    var glucoseInput = document.getElementById("fasting_glucose");

    function syncUnit() {

        var chosen = document.querySelector('input[name="glucose_unit"]:checked');
        var unit   = units[chosen ? chosen.value : "mg_dl"];

        unitLabel.textContent = unit.label;
        hint.textContent      = unit.hint;

        glucoseInput.setAttribute("data-step", unit.step);
    }

    radios.forEach(function (radio) {
        radio.addEventListener("change", syncUnit);
    });

    syncUnit();


    // Up / down buttons inside the number boxes
    document.querySelectorAll(".hu-spin").forEach(function (box) {

        var input = box.querySelector("input");

        box.querySelectorAll("button[data-dir]").forEach(function (button) {

            button.addEventListener("click", function () {

                var direction = parseInt(button.getAttribute("data-dir"), 10);
                var step      = parseFloat(input.getAttribute("data-step")) || 1;
                var current   = parseFloat(input.value);

                if (isNaN(current)) {
                    current = 0;
                }

                var next = Math.max(0, Math.round((current + direction * step) * 100) / 100);

                input.value = next;
                input.focus();
            });
        });
    });


    // After calculating, bring the result into view
    var result = document.getElementById("result");

    if (result) {

        var calm = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

        result.scrollIntoView({
            behavior: calm ? "auto" : "smooth",
            block: "center"
        });
    }

})();
</script>

</body>

</html>
