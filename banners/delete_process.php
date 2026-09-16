<?php
require '../includes/auth.php';
require '../config/database.php';
require_role(array('admin'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

verify_csrf_token();

$banner_id = isset($_POST['id']) ? $_POST['id'] : '';

if (is_numeric($banner_id)) {
    $delete_stmt = mysqli_prepare($connection, 'DELETE FROM banners WHERE id = ?');
    mysqli_stmt_bind_param($delete_stmt, 'i', $banner_id);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);
}

$_SESSION['success'] = 'Banner deleted.';
header('Location: index.php');
exit();