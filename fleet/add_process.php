<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require_role(array('admin'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add.php');
    exit();
}

verify_csrf_token();

$make = isset($_POST['make']) ? trim($_POST['make']) : '';
$model = isset($_POST['model']) ? trim($_POST['model']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$plate_number = isset($_POST['plate_number']) ? trim($_POST['plate_number']) : '';
$daily_rate = isset($_POST['daily_rate']) ? $_POST['daily_rate'] : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$location_name = isset($_POST['location_name']) ? trim($_POST['location_name']) : '';
$latitude = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? $_POST['latitude'] : null;
$longitude = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? $_POST['longitude'] : null;

$allowed_motorcycle_types = array('scooter', 'underbone', 'sport_bike', 'naked', 'cruiser');
$motorcycle_type = ($category === 'motorcycle' && isset($_POST['motorcycle_type']) && in_array($_POST['motorcycle_type'], $allowed_motorcycle_types))
    ? $_POST['motorcycle_type']
    : null;
$is_scooter = $motorcycle_type !== null ? ($motorcycle_type === 'scooter' ? 1 : 0) : null;
$is_sports = isset($_POST['is_sports']) ? 1 : 0;
$transmission = isset($_POST['transmission']) ? trim($_POST['transmission']) : '';

$errors = array();
$allowed_categories = array('sedan', 'suv', 'van', 'pickup_truck', 'motorcycle', 'coupe');
$allowed_transmissions = array('automatic', 'manual');
$allowed_statuses = array('available', 'maintenance', 'retired');

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
    $check_stmt = mysqli_prepare($connection, 'SELECT id FROM vehicles WHERE plate_number = ?');
    mysqli_stmt_bind_param($check_stmt, 's', $plate_number);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $errors[] = 'A vehicle with that plate number already exists.';
    }

    mysqli_stmt_close($check_stmt);
}

$uploaded_image_paths = array();

if (empty($errors) && isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
    $allowed_types = array('image/jpeg', 'image/png', 'image/webp');
    $file_count = count($_FILES['images']['name']);

    for ($i = 0; $i < $file_count; $i++) {
        if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $file_type = mime_content_type($_FILES['images']['tmp_name'][$i]);

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = 'Each photo must be a JPEG, PNG, or WEBP file.';
            break;
        }

        if ($_FILES['images']['size'][$i] > 5 * 1024 * 1024) {
            $errors[] = 'Each photo must be smaller than 5MB.';
            break;
        }

        $new_filename = generate_upload_filename($category, $make, $model, $_FILES['images']['name'][$i]);
        $destination = '../uploads/vehicles/' . $new_filename;

        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $destination)) {
            $uploaded_image_paths[] = 'uploads/vehicles/' . $new_filename;
        } else {
            $errors[] = 'Failed to upload one of the vehicle photos.';
        }
    }
}

$image_path = !empty($uploaded_image_paths) ? $uploaded_image_paths[0] : null;

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: add.php');
    exit();
}

$insert_stmt = mysqli_prepare($connection, 'INSERT INTO vehicles (make, model, category, is_scooter, motorcycle_type, is_sports, transmission, plate_number, daily_rate, image_path, status, location_name, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
mysqli_stmt_bind_param($insert_stmt, 'sssisissdsssdd', $make, $model, $category, $is_scooter, $motorcycle_type, $is_sports, $transmission, $plate_number, $daily_rate, $image_path, $status, $location_name, $latitude, $longitude);
mysqli_stmt_execute($insert_stmt);
$new_vehicle_id = mysqli_insert_id($connection);
mysqli_stmt_close($insert_stmt);

foreach ($uploaded_image_paths as $index => $path) {
    $image_stmt = mysqli_prepare($connection, 'INSERT INTO vehicle_images (vehicle_id, image_path, sort_order) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($image_stmt, 'isi', $new_vehicle_id, $path, $index);
    mysqli_stmt_execute($image_stmt);
    mysqli_stmt_close($image_stmt);
}

$_SESSION['success'] = 'Vehicle added successfully.';
header('Location: index.php');
exit();