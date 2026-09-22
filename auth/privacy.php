<?php

require_once "../includes/ui.php";

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Privacy Policy - HOMA-IR</title>

    <link
        rel="stylesheet"
        href="../assets/homa-ui.css"
    >

</head>

<body class="hu-body">

<div class="hu-page">

    <?php hu_page_header(1); ?>

    <main class="hu-main">

        <section class="hu-card hu-legal">

            <h2 class="hu-card__title">Privacy Policy</h2>

            <!--
                TODO: This is starter text. Replace it with your own privacy
                policy, reviewed by whoever handles legal/compliance for this project.
            -->

            <h2>What we store</h2>

            <ul>
                <li>Your name and email address, and a securely hashed password.</li>
                <li>Patient details you enter (name, age, contact, city).</li>
                <li>The glucose and insulin values you calculate, and the results.</li>
            </ul>

            <h2>Signing up with Google</h2>

            <p>
                If you choose "Sign up with Google", we receive your name and
                verified email address from Google. We do not receive your Google
                password.
            </p>

            <h2>How it is used</h2>

            <p>
                Your information is used only to run your account and to save and
                show your calculations. Patient records are visible only to the
                doctor who created them.
            </p>

            <a class="hu-back" href="signup.php">Back to sign up</a>

        </section>

    </main>

</div>

</body>

</html>
