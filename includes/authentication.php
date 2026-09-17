<?php

if (!defined('ABSPATH')) {
    exit;
}


/*
|--------------------------------------------------------------------------
| Doctor Registration
|--------------------------------------------------------------------------
*/

function homa_register_doctor() {

    if (
        !isset($_POST['homa_signup_submit'])
    ) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Security Check
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_POST['homa_signup_nonce']) ||
        !wp_verify_nonce(
            $_POST['homa_signup_nonce'],
            'homa_signup_action'
        )
    ) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Form Data
    |--------------------------------------------------------------------------
    */

    $doctor_name = sanitize_text_field(
        $_POST['doctor_name'] ?? ''
    );

    $email = sanitize_email(
        $_POST['email'] ?? ''
    );

    $password = $_POST['password'] ?? '';

    $confirm_password = $_POST['confirm_password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (empty($doctor_name)) {

        wp_die('Please enter your full name.');
    }


    if (empty($email) || !is_email($email)) {

        wp_die('Please enter a valid email address.');
    }


    if (empty($password)) {

        wp_die('Please enter a password.');
    }


    if ($password !== $confirm_password) {

        wp_die('Passwords do not match.');
    }


    /*
    |--------------------------------------------------------------------------
    | Check Existing Email
    |--------------------------------------------------------------------------
    */

    if (email_exists($email)) {

        wp_die(
            'An account with this email already exists.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create WordPress User
    |--------------------------------------------------------------------------
    */

    $user_id = wp_create_user(
        $email,
        $password,
        $email
    );


    if (is_wp_error($user_id)) {

        wp_die(
            $user_id->get_error_message()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Save Doctor Name
    |--------------------------------------------------------------------------
    */

    update_user_meta(
        $user_id,
        'doctor_name',
        $doctor_name
    );


    /*
    |--------------------------------------------------------------------------
    | Login Doctor Automatically
    |--------------------------------------------------------------------------
    */

    wp_set_auth_cookie($user_id);

    wp_set_current_user($user_id);


    /*
    |--------------------------------------------------------------------------
    | Redirect After Registration
    |--------------------------------------------------------------------------
    */

    wp_safe_redirect(
        home_url('/add-patient/')
    );

    exit;
}

add_action(
    'init',
    'homa_register_doctor'
);


/*
|--------------------------------------------------------------------------
| Doctor Login
|--------------------------------------------------------------------------
*/

function homa_login_doctor() {

    if (
        !isset($_POST['homa_login_submit'])
    ) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Security Check
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_POST['homa_login_nonce']) ||
        !wp_verify_nonce(
            $_POST['homa_login_nonce'],
            'homa_login_action'
        )
    ) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Login Data
    |--------------------------------------------------------------------------
    */

    $email = sanitize_email(
        $_POST['email'] ?? ''
    );

    $password = $_POST['password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (empty($email) || empty($password)) {

        wp_die(
            'Please enter your email and password.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    $user = wp_signon(
        array(
            'user_login'    => $email,
            'user_password' => $password,
            'remember'      => true
        ),
        is_ssl()
    );


    /*
    |--------------------------------------------------------------------------
    | Check Login Error
    |--------------------------------------------------------------------------
    */

    if (is_wp_error($user)) {

        wp_die(
            'Invalid email or password.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Redirect After Login
    |--------------------------------------------------------------------------
    */

    wp_safe_redirect(
        home_url('/add-patient/')
    );

    exit;
}

add_action(
    'init',
    'homa_login_doctor'
);


/*
|--------------------------------------------------------------------------
| Doctor Logout
|--------------------------------------------------------------------------
*/

function homa_logout_doctor() {

    if (
        isset($_GET['homa_logout'])
    ) {

        wp_logout();

        wp_safe_redirect(
            home_url('/login/')
        );

        exit;
    }
}

add_action(
    'init',
    'homa_logout_doctor'
);