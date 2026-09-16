<?php
require '../includes/auth.php';
require '../config/database.php';
require_role(array('admin'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add.php');
    exit();
}

verify_csrf_token();

$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$vehicle_id = isset($_POST['vehicle_id']) && $_POST['vehicle_id'] !== '' ? $_POST['vehicle_id'] : null;
$sort_order = isset($_POST['sort_order']) && is_numeric($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;
$is_active = isset($_POST['is_active']) ? 1 : 0;

$errors = array();

if ($vehicle_id !== null && !is_numeric($vehicle_id)) {
    $errors[] = 'Invalid vehicle selected.';
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'A banner image is required.';
}

$image_path = null;

if (empty($errors)) {
    $allowed_types = array('image/jpeg', 'image/png', 'image/webp');
    $file_type = mime_content_type($_FILES['image']['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        $errors[] = 'Banner image must be a JPEG, PNG, or WEBP file.';
    } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        $errors[] = 'Banner image must be smaller than 5MB.';
    } else {
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('banner_', true) . '.' . $extension;
        $destination = '../uploads/banners/' . $new_filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            $image_path = 'uploads/banners/' . $new_filename;
        } else {
            $errors[] = 'Failed to upload banner image.';
        }
    }
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: add.php');
    exit();
}

$insert_stmt = mysqli_prepare($connection, 'INSERT INTO banners (vehicle_id, image_path, title, sort_order, is_active) VALUES (?, ?, ?, ?, ?)');
mysqli_stmt_bind_param($insert_stmt, 'issii', $vehicle_id, $image_path, $title, $sort_order, $is_active);
mysqli_stmt_execute($insert_stmt);
mysqli_stmt_close($insert_stmt);

$_SESSION['success'] = 'Banner added successfully.';
header('Location: index.php');
exit();