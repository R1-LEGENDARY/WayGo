<?php
require '../includes/auth.php';
require '../config/database.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: change_password.php');
    exit();
}

verify_csrf_token();

$current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
$new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
$confirm_new_password = isset($_POST['confirm_new_password']) ? $_POST['confirm_new_password'] : '';

$errors = array();

$stmt = mysqli_prepare($connection, 'SELECT password FROM users WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user || !password_verify($current_password, $user['password'])) {
    $errors[] = 'Current password is incorrect.';
}

if (strlen($new_password) < 8) {
    $errors[] = 'New password must be at least 8 characters.';
}

if ($new_password !== $confirm_new_password) {
    $errors[] = 'New passwords do not match.';
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: change_password.php');
    exit();
}

$new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

$update_stmt = mysqli_prepare($connection, 'UPDATE users SET password = ? WHERE id = ?');
mysqli_stmt_bind_param($update_stmt, 'si', $new_password_hash, $_SESSION['user_id']);
mysqli_stmt_execute($update_stmt);
mysqli_stmt_close($update_stmt);

$_SESSION['success'] = 'Password updated successfully.';
header('Location: change_password.php');
exit();