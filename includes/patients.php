<?php

if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| Create Patient Database Table
|--------------------------------------------------------------------------
*/

function homa_create_patient_table() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'homa_patients';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        doctor_id BIGINT UNSIGNED NOT NULL,
        name VARCHAR(150) NOT NULL,
        age INT UNSIGNED NOT NULL,
        contact VARCHAR(30) NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY doctor_id (doctor_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta($sql);
}


/*
|--------------------------------------------------------------------------
| Add Patient
|--------------------------------------------------------------------------
*/

function homa_add_patient() {

    if (!isset($_POST['homa_add_patient_submit'])) {
        return;
    }

    if (!is_user_logged_in()) {
        return;
    }

    if (
        !isset($_POST['homa_patient_nonce']) ||
        !wp_verify_nonce(
            $_POST['homa_patient_nonce'],
            'homa_add_patient_action'
        )
    ) {
        return;
    }

    global $wpdb;

    $table_name = $wpdb->prefix . 'homa_patients';

    $doctor_id = get_current_user_id();

    $name = sanitize_text_field($_POST['patient_name'] ?? '');
    $age = absint($_POST['patient_age'] ?? 0);
    $contact = sanitize_text_field($_POST['patient_contact'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (empty($name)) {
        return;
    }

    if ($age <= 0) {
        return;
    }

    if (empty($contact)) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Insert Patient
    |--------------------------------------------------------------------------
    */

    $wpdb->insert(
        $table_name,
        array(
            'doctor_id'  => $doctor_id,
            'name'       => $name,
            'age'        => $age,
            'contact'    => $contact,
            'created_at' => current_time('mysql')
        ),
        array(
            '%d',
            '%s',
            '%d',
            '%s',
            '%s'
        )
    );


    /*
    |--------------------------------------------------------------------------
    | Get Newly Created Patient
    |--------------------------------------------------------------------------
    */

    $patient_id = $wpdb->insert_id;


    /*
    |--------------------------------------------------------------------------
    | Go To Calculator
    |--------------------------------------------------------------------------
    */

    if ($patient_id) {

        wp_safe_redirect(
            home_url('/homa-calculator/?patient_id=' . $patient_id)
        );

        exit;
    }
}

add_action('init', 'homa_add_patient');