<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/addons.php';
require '../includes/mailer.php';
require_role(array('customer'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vehicles/browse.php');
    exit();
}

verify_csrf_token();

$vehicle_id = isset($_POST['vehicle_id']) ? $_POST['vehicle_id'] : '';
$start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
$end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
$submitted_addons = isset($_POST['addons']) ? $_POST['addons'] : array();
$customer_id = $_SESSION['user_id'];

$fulfillment_type = isset($_POST['fulfillment_type']) && $_POST['fulfillment_type'] === 'delivery' ? 'delivery' : 'pickup';
$delivery_time = null;
$contact_number = null;
$delivery_region = null;
$delivery_province = null;
$delivery_city = null;
$delivery_barangay = null;
$delivery_address_line = null;

if ($fulfillment_type === 'delivery') {
    $delivery_time = isset($_POST['delivery_time']) ? trim($_POST['delivery_time']) : '';
    $contact_number = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
    $delivery_region = isset($_POST['delivery_region']) ? trim($_POST['delivery_region']) : '';
    $delivery_province = isset($_POST['delivery_province']) ? trim($_POST['delivery_province']) : '';
    $delivery_city = isset($_POST['delivery_city']) ? trim($_POST['delivery_city']) : '';
    $delivery_barangay = isset($_POST['delivery_barangay']) ? trim($_POST['delivery_barangay']) : '';
    $delivery_address_line = isset($_POST['delivery_address_line']) ? trim($_POST['delivery_address_line']) : '';
}

$field_errors = array();

if (!is_numeric($vehicle_id)) {
    $field_errors['vehicle_id'] = 'Invalid vehicle selected.';
}

$start_timestamp = strtotime($start_date);
$end_timestamp = strtotime($end_date);

if ($start_timestamp === false || $end_timestamp === false) {
    $field_errors['dates'] = 'Please provide valid dates.';
} elseif ($start_timestamp > $end_timestamp) {
    $field_errors['dates'] = 'Pickup date must be on or before the return date.';
} elseif ($start_timestamp < strtotime(date('Y-m-d'))) {
    $field_errors['dates'] = 'Pickup date cannot be in the past.';
}

$selected_addons = array();
foreach ($submitted_addons as $addon_key) {
    if (isset($addon_catalog[$addon_key])) {
        $selected_addons[] = $addon_key;
    }
}

if ($fulfillment_type === 'delivery') {
    if (empty($delivery_time)) {
        $field_errors['delivery_time'] = 'Please choose a delivery time.';
    }
    if (empty($contact_number)) {
        $field_errors['contact_number'] = 'Contact number is required for delivery.';
    }
    if (empty($delivery_region)) {
        $field_errors['delivery_region'] = 'Region is required.';
    }
    if (empty($delivery_province)) {
        $field_errors['delivery_province'] = 'Province is required.';
    }
    if (empty($delivery_city)) {
        $field_errors['delivery_city'] = 'City is required.';
    }
    if (empty($delivery_barangay)) {
        $field_errors['delivery_barangay'] = 'Barangay is required.';
    }
    if (empty($delivery_address_line)) {
        $field_errors['delivery_address_line'] = 'Street/House No./Landmark is required.';
    }
}

mysqli_begin_transaction($connection);

try {
    $lock_stmt = mysqli_prepare($connection, 'SELECT id, daily_rate, status FROM vehicles WHERE id = ? FOR UPDATE');
    mysqli_stmt_bind_param($lock_stmt, 'i', $vehicle_id);
    mysqli_stmt_execute($lock_stmt);
    $lock_result = mysqli_stmt_get_result($lock_stmt);
    $vehicle = mysqli_fetch_assoc($lock_result);
    mysqli_stmt_close($lock_stmt);

    if (!$vehicle || $vehicle['status'] !== 'available') {
        throw new Exception('This vehicle is no longer available.');
    }

    $overlap_stmt = mysqli_prepare($connection, "SELECT id FROM bookings
        WHERE vehicle_id = ?
        AND status != 'cancelled'
        AND start_date <= ?
        AND end_date >= ?
        FOR UPDATE");
    mysqli_stmt_bind_param($overlap_stmt, 'iss', $vehicle_id, $end_date, $start_date);
    mysqli_stmt_execute($overlap_stmt);
    mysqli_stmt_store_result($overlap_stmt);

    if (mysqli_stmt_num_rows($overlap_stmt) > 0) {
        mysqli_stmt_close($overlap_stmt);
        throw new Exception('This vehicle is already booked for part of that date range. Please choose different dates.');
    }

    mysqli_stmt_close($overlap_stmt);

    $days = ($end_timestamp - $start_timestamp) / 86400;
    $days = $days < 1 ? 1 : $days;
    $vehicle_total = $days * $vehicle['daily_rate'];

    $addon_total = 0;
    foreach ($selected_addons as $addon_key) {
        $addon_total += $addon_catalog[$addon_key]['price'];
    }

    $total_price = $vehicle_total + $addon_total;

    $insert_stmt = mysqli_prepare($connection, "INSERT INTO bookings
        (vehicle_id, customer_id, start_date, end_date, total_price, status, fulfillment_type, delivery_time, contact_number, delivery_region, delivery_province, delivery_city, delivery_barangay, delivery_address_line)
        VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($insert_stmt, 'iissdssssssss', $vehicle_id, $customer_id, $start_date, $end_date, $total_price, $fulfillment_type, $delivery_time, $contact_number, $delivery_region, $delivery_province, $delivery_city, $delivery_barangay, $delivery_address_line);
    mysqli_stmt_execute($insert_stmt);
    $booking_id = mysqli_insert_id($connection);
    mysqli_stmt_close($insert_stmt);

    foreach ($selected_addons as $addon_key) {
        $addon_label = $addon_catalog[$addon_key]['label'];
        $addon_price = $addon_catalog[$addon_key]['price'];

        $addon_stmt = mysqli_prepare($connection, 'INSERT INTO booking_addons (booking_id, addon_name, addon_price) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($addon_stmt, 'isd', $booking_id, $addon_label, $addon_price);
        mysqli_stmt_execute($addon_stmt);
        mysqli_stmt_close($addon_stmt);
    }

    mysqli_commit($connection);

    $customer_stmt = mysqli_prepare($connection, 'SELECT name, email FROM users WHERE id = ?');
    mysqli_stmt_bind_param($customer_stmt, 'i', $customer_id);
    mysqli_stmt_execute($customer_stmt);
    $customer_result = mysqli_stmt_get_result($customer_stmt);
    $customer = mysqli_fetch_assoc($customer_result);
    mysqli_stmt_close($customer_stmt);

    $vehicle_name_stmt = mysqli_prepare($connection, 'SELECT make, model FROM vehicles WHERE id = ?');
    mysqli_stmt_bind_param($vehicle_name_stmt, 'i', $vehicle_id);
    mysqli_stmt_execute($vehicle_name_stmt);
    $vehicle_name_result = mysqli_stmt_get_result($vehicle_name_stmt);
    $vehicle_info = mysqli_fetch_assoc($vehicle_name_result);
    mysqli_stmt_close($vehicle_name_stmt);

    send_booking_confirmation_email(
        $customer['email'],
        $customer['name'],
        $vehicle_info['make'] . ' ' . $vehicle_info['model'],
        $start_date,
        $end_date,
        $total_price
    );

    $_SESSION['success'] = 'Booking request submitted and is pending confirmation. Check your email.';
    header('Location: my_bookings.php');
    exit();

} catch (Exception $e) {
    mysqli_rollback($connection);
    $_SESSION['errors'] = array($e->getMessage());
    header('Location: ../vehicles/show.php?vehicle_id=' . $vehicle_id . '&start_date=' . $start_date . '&end_date=' . $end_date);
    exit();
}