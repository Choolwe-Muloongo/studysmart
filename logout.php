<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Auth.php';

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    $auth = new Auth();
    $auth->logout();
}

// Redirect to login page
header('Location: login.php');
exit();
?>
