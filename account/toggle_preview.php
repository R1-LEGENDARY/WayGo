<?php
require '../includes/auth.php';
require_role(array('admin'));

if (isset($_SESSION['preview_role']) && $_SESSION['preview_role'] === 'customer') {
    unset($_SESSION['preview_role']);
    header('Location: ../dashboard/index.php');
} else {
    $_SESSION['preview_role'] = 'customer';
    header('Location: ../vehicles/browse.php');
}
exit();