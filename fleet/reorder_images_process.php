<?php
require '../includes/auth.php';
require '../config/database.php';
require_role(array('admin'));

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false));
    exit();
}

verify_csrf_token();

$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true);

$vehicle_id = isset($data['vehicle_id']) ? $data['vehicle_id'] : '';
$ordered_ids = isset($data['ordered_ids']) ? $data['ordered_ids'] : array();

if (!is_numeric($vehicle_id) || !is_array($ordered_ids) || empty($ordered_ids)) {
    http_response_code(400);
    echo json_encode(array('success' => false));
    exit();
}

foreach ($ordered_ids as $position => $image_id) {
    if (!is_numeric($image_id)) {
        continue;
    }

    $update_stmt = mysqli_prepare($connection, 'UPDATE vehicle_images SET sort_order = ? WHERE id = ? AND vehicle_id = ?');
    mysqli_stmt_bind_param($update_stmt, 'iii', $position, $image_id, $vehicle_id);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);
}

$first_stmt = mysqli_prepare($connection, 'SELECT image_path FROM vehicle_images WHERE vehicle_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1');
mysqli_stmt_bind_param($first_stmt, 'i', $vehicle_id);
mysqli_stmt_execute($first_stmt);
$first_result = mysqli_stmt_get_result($first_stmt);
$first_image = mysqli_fetch_assoc($first_result);
mysqli_stmt_close($first_stmt);

if ($first_image) {
    $sync_stmt = mysqli_prepare($connection, 'UPDATE vehicles SET image_path = ? WHERE id = ?');
    mysqli_stmt_bind_param($sync_stmt, 'si', $first_image['image_path'], $vehicle_id);
    mysqli_stmt_execute($sync_stmt);
    mysqli_stmt_close($sync_stmt);
}

echo json_encode(array('success' => true));
exit();