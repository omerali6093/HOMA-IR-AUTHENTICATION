<?php

/**
 * Plugin Name: HOMA-IR Calculator
 * Description: HOMA-IR Calculator for doctors.
 * Version: 1.0.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit;
}


//Plugin Constants
define(
    'HOMA_PLUGIN_PATH',
    plugin_dir_path(__FILE__)
);

define(
    'HOMA_PLUGIN_URL',
    plugin_dir_url(__FILE__)
);



// load authentication files
require_once HOMA_PLUGIN_PATH . 'includes/authentication.php';
require_once HOMA_PLUGIN_PATH . 'includes/patients.php';


// load css file
function homa_enqueue_styles() {

    wp_enqueue_style(
        'homa-style',
        HOMA_PLUGIN_URL . 'assets/style.css',
        array(),
        '1.0.0'
    );
}

add_action(
    'wp_enqueue_scripts',
    'homa_enqueue_styles'
);


// this function is generating the signup code
function homa_signup_shortcode() {

    ob_start();

    include HOMA_PLUGIN_PATH . 'templates/signup.php';

    return ob_get_clean();
}

add_shortcode(
    'homa_signup',
    'homa_signup_shortcode'
);





// // this function is generating the login code
function homa_login_shortcode() {

    ob_start();

    include HOMA_PLUGIN_PATH . 'templates/login.php';

    return ob_get_clean();
}

add_shortcode(
    'homa_login',
    'homa_login_shortcode'
);

// this is function is generation is patient shortcode
function homa_patient_form_shortcode() {

    if (!is_user_logged_in()) {

        return '<p>Please login as a doctor first.</p>';
    }

    ob_start();

    include HOMA_PLUGIN_PATH . 'templates/patient-form.php';

    return ob_get_clean();
}

add_shortcode('homa_patient_form', 'homa_patient_form_shortcode');



function homa_activate_plugin() {

    homa_create_patient_table();
}

register_activation_hook(
    __FILE__,
    'homa_activate_plugin'
);