<?php

if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();

?>

<div class="homa-page">

    <div class="homa-card">

        <div class="homa-header">

            <h1>Add Patient</h1>

            <p>
                Welcome, Dr. <?php echo esc_html($current_user->display_name); ?>
            </p>

        </div>


        <form method="POST" class="homa-patient-form">

            <?php
            wp_nonce_field(
                'homa_add_patient_action',
                'homa_patient_nonce'
            );
            ?>


            <div class="homa-form-group">

                <label for="patient_name">
                    Patient Name
                </label>

                <input
                    type="text"
                    id="patient_name"
                    name="patient_name"
                    placeholder="Enter patient name"
                    required
                >

            </div>


            <div class="homa-form-group">

                <label for="patient_age">
                    Age
                </label>

                <input
                    type="number"
                    id="patient_age"
                    name="patient_age"
                    placeholder="Enter patient age"
                    min="1"
                    max="150"
                    required
                >

            </div>


            <div class="homa-form-group">

                <label for="patient_contact">
                    Contact Number
                </label>

                <input
                    type="tel"
                    id="patient_contact"
                    name="patient_contact"
                    placeholder="03XX XXXXXXX"
                    required
                >

            </div>


            <button
                type="submit"
                name="homa_add_patient_submit"
                class="homa-button"
            >
                Add Patient & Continue
            </button>

        </form>

    </div>

</div>