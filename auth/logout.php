<?php
require '../includes/auth.php';

$was_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$_SESSION = array();
session_destroy();

if ($was_admin) {
    header('Location: login.php');
} else {
    header('Location: ../vehicles/browse.php');
}
exit();