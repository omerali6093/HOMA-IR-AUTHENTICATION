<?php

if (!defined('ABSPATH')) {
    exit;
}

// function for doctor registration
function homa_register_doctor() {

    if (
        !isset($_POST['homa_signup_submit'])
    ) {
        return;
    }


    // Security Check

    if (
        !isset($_POST['homa_signup_nonce']) ||
        !wp_verify_nonce(
            $_POST['homa_signup_nonce'],
            'homa_signup_action'
        )
    ) {
        return;
    }


    // form data
    $doctor_name = sanitize_text_field(
        $_POST['doctor_name'] ?? ''
    );

    $email = sanitize_email(
        $_POST['email'] ?? ''
    );

    $password = $_POST['password'] ?? '';

    $confirm_password = $_POST['confirm_password'] ?? '';


    // conditions wheter is correct or not
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


    if (email_exists($email)) {

        wp_die(
            'An account with this email already exists.'
        );
    }

     

    // create users in database
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


    // stores doctor name
    update_user_meta(
        $user_id,
        'doctor_name',
        $doctor_name
    );


    // login doctor by itself
    wp_set_auth_cookie($user_id);

    wp_set_current_user($user_id);


    wp_safe_redirect(
        home_url('/add-patient/')
    );

    exit;
}

add_action(
    'init',
    'homa_register_doctor'
);


  // doctor login function
function homa_login_doctor() {

    if (
        !isset($_POST['homa_login_submit'])
    ) {
        return;
    }


    
    // SECURITY CHECK
    if (
        !isset($_POST['homa_login_nonce']) ||
        !wp_verify_nonce(
            $_POST['homa_login_nonce'],
            'homa_login_action'
        )
    ) {
        return;
    }


    // get login data details
    $email = sanitize_email(
        $_POST['email'] ?? ''
    );

    $password = $_POST['password'] ?? '';


    if (empty($email) || empty($password)) {

        wp_die(
            'Please enter your email and password.'
        );
    }

    $user = wp_signon(
        array(
            'user_login'    => $email,
            'user_password' => $password,
            'remember'      => true
        ),
        is_ssl()
    );


    // check for error while login
    if (is_wp_error($user)) {

        wp_die(
            'Invalid email or password.'
        );
    }


    wp_safe_redirect(
        home_url('/add-patient/')
    );

    exit;
}

add_action(
    'init',
    'homa_login_doctor'
);



  // doctor logout function

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
)