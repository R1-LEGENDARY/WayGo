<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit();
}

verify_csrf_token();

$first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
$redirect = isset($_POST['redirect']) ? trim($_POST['redirect']) : '';

$field_errors = array();

if (empty($first_name)) {
    $field_errors['first_name'] = 'Please enter your first name.';
}

if (empty($last_name)) {
    $field_errors['last_name'] = 'Please enter your last name.';
}

if (empty($email)) {
    $field_errors['email'] = 'Please enter your email.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $field_errors['email'] = 'That doesn\'t look like a valid email.';
} elseif (!email_domain_can_receive_mail($email)) {
    $field_errors['email'] = 'That email looks like it has a typo. Please double-check it.';
}

if (empty($password)) {
    $field_errors['password'] = 'Please enter a password.';
} elseif (strlen($password) < 8) {
    $field_errors['password'] = 'Password should be at least 8 characters.';
}

if (empty($confirm_password)) {
    $field_errors['confirm_password'] = 'Please confirm your password.';
} elseif ($password !== $confirm_password) {
    $field_errors['confirm_password'] = 'Passwords don\'t match.';
}

if (empty($field_errors) || (!isset($field_errors['email']))) {
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $check_stmt = mysqli_prepare($connection, 'SELECT id FROM users WHERE email = ?');
        mysqli_stmt_bind_param($check_stmt, 's', $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $field_errors['email'] = 'An account with that email already exists.';
        }
        mysqli_stmt_close($check_stmt);
    }
}

if (!empty($field_errors)) {
    $_SESSION['field_errors'] = $field_errors;
    $_SESSION['old_input'] = array('first_name' => $first_name, 'last_name' => $last_name, 'email' => $email);
    header('Location: register.php' . (!empty($redirect) ? '?redirect=' . urlencode($redirect) : ''));
    exit();
}

$full_name = $first_name . ' ' . $last_name;
$password_hash = password_hash($password, PASSWORD_BCRYPT);
$role = 'customer';

$insert_stmt = mysqli_prepare($connection, 'INSERT INTO users (name, first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?, ?)');
mysqli_stmt_bind_param($insert_stmt, 'ssssss', $full_name, $first_name, $last_name, $email, $password_hash, $role);
mysqli_stmt_execute($insert_stmt);
mysqli_stmt_close($insert_stmt);

$_SESSION['success'] = 'Account created. Please log in.';
header('Location: login.php' . (!empty($redirect) ? '?redirect=' . urlencode($redirect) : ''));
exit();