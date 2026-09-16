<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/mailer.php';
require_role(array('admin', 'staff'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage.php');
    exit();
}

verify_csrf_token();

$booking_id = isset($_POST['booking_id']) ? $_POST['booking_id'] : '';
$new_status = isset($_POST['new_status']) ? trim($_POST['new_status']) : '';

$allowed_statuses = array('pending', 'confirmed', 'active', 'completed', 'cancelled');

$legal_transitions = array(
    'pending' => array('confirmed', 'cancelled'),
    'confirmed' => array('active', 'cancelled'),
    'active' => array('completed'),
);

if (!is_numeric($booking_id) || !in_array($new_status, $allowed_statuses)) {
    $_SESSION['errors'] = array('Invalid request.');
    header('Location: manage.php');
    exit();
}

$stmt = mysqli_prepare($connection, 'SELECT status FROM bookings WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $booking_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$booking) {
    $_SESSION['errors'] = array('Booking not found.');
    header('Location: manage.php');
    exit();
}

$current_status = $booking['status'];
$allowed_next = isset($legal_transitions[$current_status]) ? $legal_transitions[$current_status] : array();

if (!in_array($new_status, $allowed_next)) {
    $_SESSION['errors'] = array("Cannot move a booking from '$current_status' to '$new_status'.");
    header('Location: manage.php');
    exit();
}

$update_stmt = mysqli_prepare($connection, 'UPDATE bookings SET status = ? WHERE id = ?');
mysqli_stmt_bind_param($update_stmt, 'si', $new_status, $booking_id);
mysqli_stmt_execute($update_stmt);
mysqli_stmt_close($update_stmt);

if (in_array($new_status, array('confirmed', 'active', 'completed'))) {
    $details_stmt = mysqli_prepare($connection, "SELECT bookings.start_date, bookings.end_date,
        users.name AS customer_name, users.email AS customer_email,
        vehicles.make, vehicles.model
        FROM bookings
        JOIN users ON users.id = bookings.customer_id
        JOIN vehicles ON vehicles.id = bookings.vehicle_id
        WHERE bookings.id = ?");
    mysqli_stmt_bind_param($details_stmt, 'i', $booking_id);
    mysqli_stmt_execute($details_stmt);
    $details_result = mysqli_stmt_get_result($details_stmt);
    $details = mysqli_fetch_assoc($details_result);
    mysqli_stmt_close($details_stmt);

    if ($details) {
        send_booking_status_email(
            $details['customer_email'],
            $details['customer_name'],
            $details['make'] . ' ' . $details['model'],
            $details['start_date'],
            $details['end_date'],
            $new_status
        );
    }
}

$_SESSION['success'] = "Booking for {$details['make']} {$details['model']} updated to " . ucfirst($new_status) . ".";
header('Location: manage.php');
exit();