<?php

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="homa-auth-container">

    <div class="homa-auth-card">

        <h2>Doctor Login</h2>

        <p>
            Sign in to access your patients and
            HOMA-IR calculator.
        </p>


        <form method="POST">

            <?php
            wp_nonce_field(
                'homa_login_action',
                'homa_login_nonce'
            );
            ?>


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
                    placeholder="Enter your password"
                    required
                >

            </div>


            <button
                type="submit"
                name="homa_login_submit"
                class="homa-button"
            >
                Sign In
            </button>

        </form>


        <p class="homa-auth-footer">

            Don't have an account?

            <a href="<?php echo esc_url(
                home_url('/signup/')
            ); ?>">
                Create Account
            </a>

        </p>

    </div>

</div>