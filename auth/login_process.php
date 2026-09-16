<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

verify_csrf_token();

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$redirect = isset($_POST['redirect']) ? trim($_POST['redirect']) : '';

$field_errors = array();

if (empty($email)) {
    $field_errors['email'] = 'Please enter your email.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $field_errors['email'] = 'That doesn\'t look like a valid email.';
}

if (empty($password)) {
    $field_errors['password'] = 'Please enter your password.';
}

if (empty($field_errors)) {
    $stmt = mysqli_prepare($connection, 'SELECT id, name, password, role, email FROM users WHERE email = ?');
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user) {
        $field_errors['email'] = 'We couldn\'t find an account with that email.';
    } elseif (!password_verify($password, $user['password'])) {
        $field_errors['password'] = 'That password doesn\'t match. Please try again.';
    }
}

if (!empty($field_errors)) {
    $_SESSION['field_errors'] = $field_errors;
    $_SESSION['old_input'] = array('email' => $email);
    header('Location: login.php' . (!empty($redirect) ? '?redirect=' . urlencode($redirect) : ''));
    exit();
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['role'] = $user['role'];
$_SESSION['email'] = $user['email'];

$role_defaults = array(
    'admin' => '../dashboard/index.php',
    'customer' => '../vehicles/browse.php',
);
$default_destination = isset($role_defaults[$user['role']]) ? $role_defaults[$user['role']] : '../vehicles/browse.php';

$destination = safe_internal_redirect($redirect, $default_destination);
header('Location: ' . $destination);
exit();