<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require_role(array('admin'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

verify_csrf_token();

$vehicle_id = isset($_POST['id']) ? $_POST['id'] : '';

if (!is_numeric($vehicle_id)) {
    header('Location: index.php');
    exit();
}

$vehicle_id = (int) $vehicle_id;

$fetch_stmt = mysqli_prepare($connection, 'SELECT image_path FROM vehicles WHERE id = ?');
mysqli_stmt_bind_param($fetch_stmt, 'i', $vehicle_id);
mysqli_stmt_execute($fetch_stmt);
$fetch_result = mysqli_stmt_get_result($fetch_stmt);
$vehicle = mysqli_fetch_assoc($fetch_result);
mysqli_stmt_close($fetch_stmt);

if (!$vehicle) {
    $_SESSION['success'] = 'Vehicle not found.';
    header('Location: index.php');
    exit();
}

$main_image = $vehicle['image_path'];

$gallery_stmt = mysqli_prepare($connection, 'SELECT image_path FROM vehicle_images WHERE vehicle_id = ?');
mysqli_stmt_bind_param($gallery_stmt, 'i', $vehicle_id);
mysqli_stmt_execute($gallery_stmt);
$gallery_result = mysqli_stmt_get_result($gallery_stmt);
$gallery_images = mysqli_fetch_all($gallery_result, MYSQLI_ASSOC);
mysqli_stmt_close($gallery_stmt);

$tables = array(
    'DELETE FROM booking_addons WHERE booking_id IN (SELECT id FROM bookings WHERE vehicle_id = ?)' => 'i',
    'DELETE FROM bookings WHERE vehicle_id = ?' => 'i',
    'DELETE FROM vehicle_images WHERE vehicle_id = ?' => 'i',
    'DELETE FROM banners WHERE vehicle_id = ?' => 'i',
    'DELETE FROM favorites WHERE vehicle_id = ?' => 'i',
    'DELETE FROM vehicles WHERE id = ?' => 'i',
);

foreach ($tables as $sql => $type) {
    $stmt = mysqli_prepare($connection, $sql);
    if ($type === 'i') {
        mysqli_stmt_bind_param($stmt, 'i', $vehicle_id);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

$base_dir = dirname(__DIR__);

if (!empty($main_image) && $main_image === str_replace('\\', '/', $main_image)) {
    $file = $base_dir . '/' . ltrim($main_image, '/');
    if (is_file($file)) {
        @unlink($file);
    }
}

foreach ($gallery_images as $img) {
    if (!empty($img['image_path'])) {
        $file = $base_dir . '/' . ltrim($img['image_path'], '/');
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

$_SESSION['success'] = 'Vehicle deleted.';
header('Location: index.php');
exit();
