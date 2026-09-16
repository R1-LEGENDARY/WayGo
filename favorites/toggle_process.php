<?php
require '../includes/auth.php';
require '../config/database.php';
require_role(array('customer'));

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
$customer_id = $_SESSION['user_id'];

if (!is_numeric($vehicle_id)) {
    http_response_code(400);
    echo json_encode(array('success' => false));
    exit();
}

$check_stmt = mysqli_prepare($connection, 'SELECT id FROM favorites WHERE customer_id = ? AND vehicle_id = ?');
mysqli_stmt_bind_param($check_stmt, 'ii', $customer_id, $vehicle_id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);
$exists = mysqli_stmt_num_rows($check_stmt) > 0;
mysqli_stmt_close($check_stmt);

if ($exists) {
    $delete_stmt = mysqli_prepare($connection, 'DELETE FROM favorites WHERE customer_id = ? AND vehicle_id = ?');
    mysqli_stmt_bind_param($delete_stmt, 'ii', $customer_id, $vehicle_id);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);
    echo json_encode(array('success' => true, 'favorited' => false));
} else {
    $insert_stmt = mysqli_prepare($connection, 'INSERT INTO favorites (customer_id, vehicle_id) VALUES (?, ?)');
    mysqli_stmt_bind_param($insert_stmt, 'ii', $customer_id, $vehicle_id);
    mysqli_stmt_execute($insert_stmt);
    mysqli_stmt_close($insert_stmt);
    echo json_encode(array('success' => true, 'favorited' => true));
}
exit();