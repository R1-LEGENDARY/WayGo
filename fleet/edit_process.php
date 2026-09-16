<?php
require '../includes/auth.php';
require '../config/database.php';
require_role(array('admin'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

verify_csrf_token();

$vehicle_id = isset($_POST['id']) ? $_POST['id'] : '';
$make = isset($_POST['make']) ? trim($_POST['make']) : '';
$model = isset($_POST['model']) ? trim($_POST['model']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$transmission = isset($_POST['transmission']) ? trim($_POST['transmission']) : '';
$allowed_motorcycle_types = array('scooter', 'underbone', 'sport_bike', 'naked', 'cruiser');
$motorcycle_type = ($category === 'motorcycle' && isset($_POST['motorcycle_type']) && in_array($_POST['motorcycle_type'], $allowed_motorcycle_types))
    ? $_POST['motorcycle_type']
    : null;
$is_scooter = $motorcycle_type !== null ? ($motorcycle_type === 'scooter' ? 1 : 0) : null;
$is_sports = isset($_POST['is_sports']) ? 1 : 0;
$plate_number = isset($_POST['plate_number']) ? trim($_POST['plate_number']) : '';
$daily_rate = isset($_POST['daily_rate']) ? $_POST['daily_rate'] : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$location_name = isset($_POST['location_name']) ? trim($_POST['location_name']) : '';
$latitude = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? $_POST['latitude'] : null;
$longitude = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? $_POST['longitude'] : null;

$errors = array();
$allowed_categories = array('sedan', 'suv', 'van', 'pickup_truck', 'motorcycle', 'coupe');
$allowed_transmissions = array('automatic', 'manual');
$allowed_statuses = array('available', 'maintenance', 'retired');

if (!is_numeric($vehicle_id)) {
    $errors[] = 'Invalid vehicle.';
}

if (empty($make)) {
    $errors[] = 'Make is required.';
}

if (empty($model)) {
    $errors[] = 'Model is required.';
}

if (!in_array($category, $allowed_categories)) {
    $errors[] = 'Invalid category selected.';
}

if (!in_array($transmission, $allowed_transmissions)) {
    $errors[] = 'Invalid transmission selected.';
}

if (empty($plate_number)) {
    $errors[] = 'Plate number is required.';
}

if (!is_numeric($daily_rate) || $daily_rate <= 0) {
    $errors[] = 'Daily rate must be a positive number.';
}

if (!in_array($status, $allowed_statuses)) {
    $errors[] = 'Invalid status selected.';
}

if ($latitude !== null && (!is_numeric($latitude) || $latitude < -90 || $latitude > 90)) {
    $errors[] = 'Invalid latitude.';
}

if ($longitude !== null && (!is_numeric($longitude) || $longitude < -180 || $longitude > 180)) {
    $errors[] = 'Invalid longitude.';
}

if (empty($errors)) {
    $check_stmt = mysqli_prepare($connection, 'SELECT id FROM vehicles WHERE plate_number = ? AND id != ?');
    mysqli_stmt_bind_param($check_stmt, 'si', $plate_number, $vehicle_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $errors[] = 'Another vehicle already uses that plate number.';
    }

    mysqli_stmt_close($check_stmt);
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: edit.php?id=' . $vehicle_id);
    exit();
}

$uploaded_image_paths = array();

if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
    $allowed_types = array('image/jpeg', 'image/png', 'image/webp');
    $file_count = count($_FILES['images']['name']);

    for ($i = 0; $i < $file_count; $i++) {
        if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $file_type = mime_content_type($_FILES['images']['tmp_name'][$i]);

        if (!in_array($file_type, $allowed_types)) {
            continue;
        }

        if ($_FILES['images']['size'][$i] > 5 * 1024 * 1024) {
            continue;
        }

        $extension = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
        $new_filename = uniqid('vehicle_', true) . '.' . $extension;
        $destination = '../uploads/vehicles/' . $new_filename;

        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $destination)) {
            $uploaded_image_paths[] = 'uploads/vehicles/' . $new_filename;
        }
    }
}

$image_path = null;
if (!empty($uploaded_image_paths)) {
    $current_stmt = mysqli_prepare($connection, 'SELECT image_path FROM vehicles WHERE id = ?');
    mysqli_stmt_bind_param($current_stmt, 'i', $vehicle_id);
    mysqli_stmt_execute($current_stmt);
    $current_result = mysqli_stmt_get_result($current_stmt);
    $current_vehicle = mysqli_fetch_assoc($current_result);
    mysqli_stmt_close($current_stmt);

    if (empty($current_vehicle['image_path'])) {
        $image_path = $uploaded_image_paths[0];
    }
}

if ($image_path !== null) {
    $update_stmt = mysqli_prepare($connection, 'UPDATE vehicles SET make = ?, model = ?, category = ?, is_scooter = ?, motorcycle_type = ?, is_sports = ?, transmission = ?, plate_number = ?, daily_rate = ?, image_path = ?, status = ?, location_name = ?, latitude = ?, longitude = ? WHERE id = ?');
    mysqli_stmt_bind_param($update_stmt, 'sssisissdsssddi', $make, $model, $category, $is_scooter, $motorcycle_type, $is_sports, $transmission, $plate_number, $daily_rate, $image_path, $status, $location_name, $latitude, $longitude, $vehicle_id);
} else {
    $update_stmt = mysqli_prepare($connection, 'UPDATE vehicles SET make = ?, model = ?, category = ?, is_scooter = ?, motorcycle_type = ?, is_sports = ?, transmission = ?, plate_number = ?, daily_rate = ?, status = ?, location_name = ?, latitude = ?, longitude = ? WHERE id = ?');
    mysqli_stmt_bind_param($update_stmt, 'sssisissdssddi', $make, $model, $category, $is_scooter, $motorcycle_type, $is_sports, $transmission, $plate_number, $daily_rate, $status, $location_name, $latitude, $longitude, $vehicle_id);
}

mysqli_stmt_execute($update_stmt);
mysqli_stmt_close($update_stmt);

$existing_count_stmt = mysqli_prepare($connection, 'SELECT COUNT(*) AS total FROM vehicle_images WHERE vehicle_id = ?');
mysqli_stmt_bind_param($existing_count_stmt, 'i', $vehicle_id);
mysqli_stmt_execute($existing_count_stmt);
$existing_count_result = mysqli_stmt_get_result($existing_count_stmt);
$existing_image_count = (int) mysqli_fetch_assoc($existing_count_result)['total'];
mysqli_stmt_close($existing_count_stmt);

if ($existing_image_count === 0) {
    $legacy_stmt = mysqli_prepare($connection, 'SELECT image_path FROM vehicles WHERE id = ?');
    mysqli_stmt_bind_param($legacy_stmt, 'i', $vehicle_id);
    mysqli_stmt_execute($legacy_stmt);
    $legacy_result = mysqli_stmt_get_result($legacy_stmt);
    $legacy_vehicle = mysqli_fetch_assoc($legacy_result);
    mysqli_stmt_close($legacy_stmt);

    if (!empty($legacy_vehicle['image_path'])) {
        $backfill_stmt = mysqli_prepare($connection, 'INSERT INTO vehicle_images (vehicle_id, image_path, sort_order) VALUES (?, ?, 0)');
        mysqli_stmt_bind_param($backfill_stmt, 'is', $vehicle_id, $legacy_vehicle['image_path']);
        mysqli_stmt_execute($backfill_stmt);
        mysqli_stmt_close($backfill_stmt);
    }
}

$max_order_stmt = mysqli_prepare($connection, 'SELECT COALESCE(MAX(sort_order), -1) AS max_order FROM vehicle_images WHERE vehicle_id = ?');
mysqli_stmt_bind_param($max_order_stmt, 'i', $vehicle_id);
mysqli_stmt_execute($max_order_stmt);
$max_order_result = mysqli_stmt_get_result($max_order_stmt);
$next_order = (int) mysqli_fetch_assoc($max_order_result)['max_order'] + 1;
mysqli_stmt_close($max_order_stmt);

foreach ($uploaded_image_paths as $path) {
    $image_stmt = mysqli_prepare($connection, 'INSERT INTO vehicle_images (vehicle_id, image_path, sort_order) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($image_stmt, 'isi', $vehicle_id, $path, $next_order);
    mysqli_stmt_execute($image_stmt);
    mysqli_stmt_close($image_stmt);
    $next_order++;
}

$_SESSION['success'] = 'Vehicle updated successfully.';
header('Location: index.php');
exit();