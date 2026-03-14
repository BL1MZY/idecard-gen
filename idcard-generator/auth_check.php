<?php
// auth_check.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If user not logged in, kick them to login page
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
