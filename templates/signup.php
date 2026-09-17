<?php

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="homa-auth-container">

    <div class="homa-auth-card">

        <h2>Create Your Account</h2>

        <p>
            Register to access the HOMA-IR Calculator
            and manage your patients.
        </p>


        <form method="POST">

            <?php
            wp_nonce_field(
                'homa_signup_action',
                'homa_signup_nonce'
            );
            ?>


            <div class="homa-form-group">

                <label>
                    Full Name
                </label>

                <input
                    type="text"
                    name="doctor_name"
                    placeholder="Enter your full name"
                    required
                >

            </div>


            <div class="homa-form-group">

                <label>
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email address"
                    required
                >

            </div>


            <div class="homa-form-group">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Create a password"
                    required
                >

            </div>


            <div class="homa-form-group">

                <label>
                    Confirm Password
                </label>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm your password"
                    required
                >

            </div>


            <button
                type="submit"
                name="homa_signup_submit"
                class="homa-button"
            >
                Create Account
            </button>

        </form>


        <p class="homa-auth-footer">

            Already have an account?

            <a href="<?php echo esc_url(
                home_url('/login/')
            ); ?>">
                Sign In
            </a>

        </p>

    </div>

</div>