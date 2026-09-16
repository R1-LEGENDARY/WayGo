<?php
require '../includes/auth.php';
require '../config/database.php';
require_role(array('customer'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my_bookings.php');
    exit();
}

verify_csrf_token();

$booking_id = isset($_POST['booking_id']) ? $_POST['booking_id'] : '';

if (!is_numeric($booking_id)) {
    header('Location: my_bookings.php');
    exit();
}

$stmt = mysqli_prepare($connection, "SELECT id, status FROM bookings WHERE id = ? AND customer_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$booking || !in_array($booking['status'], array('pending', 'confirmed'))) {
    $_SESSION['errors'] = array('This booking can no longer be cancelled.');
    header('Location: my_bookings.php');
    exit();
}

$update_stmt = mysqli_prepare($connection, "UPDATE bookings SET status = 'cancelled' WHERE id = ?");
mysqli_stmt_bind_param($update_stmt, 'i', $booking_id);
mysqli_stmt_execute($update_stmt);
mysqli_stmt_close($update_stmt);

$_SESSION['success'] = 'Booking cancelled.';
header('Location: my_bookings.php');
exit();