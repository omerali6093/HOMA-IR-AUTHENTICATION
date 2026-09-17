<?php

session_start();

if (isset($_SESSION["doctor_id"])) {
    header("Location: calculator/index.php");
    exit;
}

header("Location: auth/login.php");
exit;