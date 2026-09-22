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

    <title>Terms &amp; Conditions - HOMA-IR</title>

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

            <h2 class="hu-card__title">Terms &amp; Conditions</h2>

            <!--
                TODO: This is starter text. Replace it with your own terms,
                reviewed by whoever handles legal/compliance for this project.
            -->

            <h2>Using the calculator</h2>

            <p>
                The HOMA-IR Calculator estimates insulin resistance from a fasting
                insulin and a fasting glucose value. It is a reference tool for
                healthcare professionals and does not replace clinical judgement.
            </p>

            <h2>Your account</h2>

            <ul>
                <li>Keep your sign-in details private and use accurate information.</li>
                <li>You are responsible for activity that happens under your account.</li>
            </ul>

            <h2>Patient information</h2>

            <p>
                Only enter patient information you are permitted to store and
                process, and handle it in line with the rules that apply to you.
            </p>

            <h2>No medical advice</h2>

            <p>
                Results are for reference only and should be interpreted together
                with other clinical information by a qualified healthcare provider.
            </p>

            <a class="hu-back" href="signup.php">Back to sign up</a>

        </section>

    </main>

</div>

</body>

</html>
