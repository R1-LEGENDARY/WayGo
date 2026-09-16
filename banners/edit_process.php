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
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$vehicle_id = isset($_POST['vehicle_id']) && $_POST['vehicle_id'] !== '' ? $_POST['vehicle_id'] : null;
$sort_order = isset($_POST['sort_order']) && is_numeric($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;
$is_active = isset($_POST['is_active']) ? 1 : 0;

$errors = array();

if (!is_numeric($banner_id)) {
    $errors[] = 'Invalid banner.';
}

if ($vehicle_id !== null && !is_numeric($vehicle_id)) {
    $errors[] = 'Invalid vehicle selected.';
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: edit.php?id=' . $banner_id);
    exit();
}

$image_path = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = array('image/jpeg', 'image/png', 'image/webp');
    $file_type = mime_content_type($_FILES['image']['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        $_SESSION['errors'] = array('Banner image must be a JPEG, PNG, or WEBP file.');
        header('Location: edit.php?id=' . $banner_id);
        exit();
    }

    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('banner_', true) . '.' . $extension;
    $destination = '../uploads/banners/' . $new_filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
        $image_path = 'uploads/banners/' . $new_filename;
    }
}

if ($image_path !== null) {
    $update_stmt = mysqli_prepare($connection, 'UPDATE banners SET title = ?, vehicle_id = ?, sort_order = ?, is_active = ?, image_path = ? WHERE id = ?');
    mysqli_stmt_bind_param($update_stmt, 'siiisi', $title, $vehicle_id, $sort_order, $is_active, $image_path, $banner_id);
} else {
    $update_stmt = mysqli_prepare($connection, 'UPDATE banners SET title = ?, vehicle_id = ?, sort_order = ?, is_active = ? WHERE id = ?');
    mysqli_stmt_bind_param($update_stmt, 'siiii', $title, $vehicle_id, $sort_order, $is_active, $banner_id);
}

mysqli_stmt_execute($update_stmt);
mysqli_stmt_close($update_stmt);

$_SESSION['success'] = 'Banner updated successfully.';
header('Location: index.php');
exit();