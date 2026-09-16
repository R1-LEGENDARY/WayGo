<?php
require '../includes/auth.php';
require '../config/database.php';
require_role(array('admin'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

verify_csrf_token();

$image_id = isset($_POST['image_id']) ? $_POST['image_id'] : '';
$vehicle_id = isset($_POST['vehicle_id']) ? $_POST['vehicle_id'] : '';

if (is_numeric($image_id) && is_numeric($vehicle_id)) {
    $delete_stmt = mysqli_prepare($connection, 'DELETE FROM vehicle_images WHERE id = ? AND vehicle_id = ?');
    mysqli_stmt_bind_param($delete_stmt, 'ii', $image_id, $vehicle_id);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);
}

echo 'ok';
exit();