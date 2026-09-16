<?php
require '../includes/auth.php';
require '../includes/helpers.php';
require '../config/database.php';
require_role(array('admin'));

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$vehicle_id = $_GET['id'];

$stmt = mysqli_prepare($connection, 'SELECT * FROM vehicles WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $vehicle_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$vehicle = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$vehicle) {
    header('Location: index.php');
    exit();
}

$default_lat = !empty($vehicle['latitude']) ? $vehicle['latitude'] : 16.6159;
$default_lng = !empty($vehicle['longitude']) ? $vehicle['longitude'] : 120.3209;

$gallery_stmt = mysqli_prepare($connection, 'SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY sort_order ASC, id ASC');
mysqli_stmt_bind_param($gallery_stmt, 'i', $vehicle_id);
mysqli_stmt_execute($gallery_stmt);
$gallery_result = mysqli_stmt_get_result($gallery_stmt);
$gallery_images = mysqli_fetch_all($gallery_result, MYSQLI_ASSOC);
mysqli_stmt_close($gallery_stmt);

$breadcrumbs = array(
    home_breadcrumb(),
    array('label' => 'Manage Fleet', 'url' => '../fleet/index.php'),
    array('label' => 'Edit ' . $vehicle['make'] . ' ' . $vehicle['model'], 'url' => null),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - Edit Vehicle</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <?php require '../includes/partials/site_header.php'; ?>
    <?php require '../includes/partials/breadcrumbs.php'; ?>
    <?php require '../includes/partials/no_changes_modal.php'; ?>

    <div class="admin-form-container">
        <div class="admin-form-card">
            <h1>Edit Vehicle</h1>
            <p class="admin-page-subtitle"><?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?></p>

            <?php if (!empty($_SESSION['errors'])) { ?>
                <ul class="admin-form-errors">
                    <?php foreach ($_SESSION['errors'] as $error) { ?>
                        <li><?= $error ?></li>
                    <?php } ?>
                </ul>
                <?php unset($_SESSION['errors']); ?>
            <?php } ?>

            <?php if (!empty($gallery_images)) { ?>
                <label>Current Photos</label>
                <p class="admin-gallery-hint">Drag to reorder. First photo is the main thumbnail. Click a photo to preview it.</p>

                <div class="admin-gallery" id="admin-gallery" data-vehicle-id="<?= (int) $vehicle_id ?>">
                    <?php foreach ($gallery_images as $index => $img) { ?>
                        <div class="admin-gallery-item" draggable="true" data-image-id="<?= $img['id'] ?>">
                            <?php if ($index === 0) { ?>
                                <span class="admin-gallery-badge">Main</span>
                            <?php } ?>
                            <img src="../<?= e($img['image_path']) ?>" alt="Vehicle photo" class="admin-gallery-preview-trigger" data-full="../<?= e($img['image_path']) ?>">
                            <button type="button" class="admin-gallery-delete-btn" data-image-id="<?= $img['id'] ?>" data-vehicle-id="<?= $vehicle_id ?>">&times;</button>
                        </div>
                    <?php } ?>
                </div>

                <dialog id="image-preview-modal">
                    <button type="button" id="close-preview-btn" class="modal-close-btn" aria-label="Close">&times;</button>
                    <img id="preview-modal-img" src="" alt="Full size preview">
                </dialog>
            <?php } elseif (!empty($vehicle['image_path'])) { ?>
                <div class="current-photo-preview">
                    <img src="../<?= e($vehicle['image_path']) ?>" alt="Current photo">
                </div>
            <?php } ?>

            <form action="edit_process.php" method="POST" enctype="multipart/form-data" id="edit-vehicle-form">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $vehicle['id'] ?>">

                <div class="admin-form-grid">
                    <div>
                        <label for="make">Make</label>
                        <input type="text" id="make" name="make" value="<?= e($vehicle['make']) ?>" required>
                    </div>
                    <div>
                        <label for="model">Model</label>
                        <input type="text" id="model" name="model" value="<?= e($vehicle['model']) ?>" required>
                    </div>

                    <div>
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="sedan" <?= $vehicle['category'] === 'sedan' ? 'selected' : '' ?>>Sedan</option>
                            <option value="suv" <?= $vehicle['category'] === 'suv' ? 'selected' : '' ?>>SUV</option>
                            <option value="van" <?= $vehicle['category'] === 'van' ? 'selected' : '' ?>>Van</option>
                            <option value="pickup_truck" <?= $vehicle['category'] === 'pickup_truck' ? 'selected' : '' ?>>Pickup Truck</option>
                            <option value="coupe" <?= $vehicle['category'] === 'coupe' ? 'selected' : '' ?>>Sports Car / Coupe</option>
                            <option value="motorcycle" <?= $vehicle['category'] === 'motorcycle' ? 'selected' : '' ?>>Motorcycle</option>
                        </select>
                    </div>
                    <div>
                        <label for="plate_number">Plate Number</label>
                        <input type="text" id="plate_number" name="plate_number" value="<?= e($vehicle['plate_number']) ?>" required>
                    </div>

                    <div id="motorcycle-type-field" class="<?= $vehicle['category'] === 'motorcycle' ? '' : 'hidden-field' ?>">
                        <label for="motorcycle_type">Motorcycle Type</label>
                        <select id="motorcycle_type" name="motorcycle_type">
                            <option value="scooter" <?= $vehicle['motorcycle_type'] === 'scooter' ? 'selected' : '' ?>>Scooter (Automatic)</option>
                            <option value="underbone" <?= $vehicle['motorcycle_type'] === 'underbone' ? 'selected' : '' ?>>Underbone (Manual Clutch)</option>
                            <option value="sport_bike" <?= $vehicle['motorcycle_type'] === 'sport_bike' ? 'selected' : '' ?>>Sport Bike (Manual Clutch)</option>
                            <option value="naked" <?= $vehicle['motorcycle_type'] === 'naked' ? 'selected' : '' ?>>Naked / Standard (Manual Clutch)</option>
                            <option value="cruiser" <?= $vehicle['motorcycle_type'] === 'cruiser' ? 'selected' : '' ?>>Cruiser (Manual Clutch)</option>
                        </select>
                    </div>

                    <div id="transmission-field" class="<?= $vehicle['category'] === 'motorcycle' ? 'hidden-field' : '' ?>">
                        <label for="transmission">Transmission</label>
                        <select id="transmission" name="transmission" required>
                            <option value="automatic" <?= $vehicle['transmission'] === 'automatic' ? 'selected' : '' ?>>Automatic</option>
                            <option value="manual" <?= $vehicle['transmission'] === 'manual' ? 'selected' : '' ?>>Manual</option>
                        </select>
                    </div>

                    <div>
                        <label for="daily_rate">Daily Rate</label>
                        <input type="number" id="daily_rate" name="daily_rate" step="0.01" min="0" value="<?= $vehicle['daily_rate'] ?>" required>
                    </div>
                    <div>
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="available" <?= $vehicle['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                            <option value="maintenance" <?= $vehicle['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                            <option value="retired" <?= $vehicle['status'] === 'retired' ? 'selected' : '' ?>>Retired</option>
                        </select>
                    </div>

                    <div class="full-width <?= $vehicle['category'] === 'motorcycle' ? 'hidden-field' : '' ?>" id="sports-field">
                        <label>
                            <input type="checkbox" id="is_sports" name="is_sports" value="1" <?= (int) $vehicle['is_sports'] === 1 ? 'checked' : '' ?>>
                            Sports variant (e.g. sports car, high-performance trim)
                        </label>
                    </div>

                    <div class="full-width">
                        <label for="images">Add More Photos (optional)</label>
                        <input type="file" id="images" name="images[]" accept="image/*" multiple>
                    </div>
                </div>

                <fieldset>
                    <legend>Pickup Location</legend>

                    <label for="location_name">Location Name / Address (auto-filled)</label>
                    <input type="text" id="location_name" name="location_name" value="<?= e($vehicle['location_name']) ?>" placeholder="Pin a location or use GPS to fill this automatically">

                    <div class="locate-btn-row">
                        <button type="button" id="locate-btn">Use My Current Location</button>
                        <span class="locate-status-text" id="locate-status"></span>
                    </div>
                    <p class="location-hint">Use GPS or click the map to automatically fill the address and update the pickup point.</p>

                    <div class="location-map-box" id="location-map"></div>

                    <input type="hidden" id="latitude" name="latitude" value="<?= $vehicle['latitude'] ?>">
                    <input type="hidden" id="longitude" name="longitude" value="<?= $vehicle['longitude'] ?>">
                </fieldset>

                <button type="submit" class="admin-form-submit">Save Changes</button>
            </form>
        </div>
    </div>

    <dialog id="save-vehicle-modal">
        <div class="modal-icon" aria-hidden="true">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#d9480f" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
        </div>
        <h2>Save Changes</h2>
        <p>Are you sure you want to save the changes to this vehicle?</p>
        <button type="button" id="save-vehicle-confirm-btn" class="modal-btn modal-btn-primary">Save Changes</button>
        <button type="button" id="save-vehicle-cancel-btn" class="modal-btn modal-btn-secondary">Cancel</button>
    </dialog>

    <script src="../js/fleet.js"></script>
    <script src="../js/no-changes-guard.js"></script>
    <script src="../js/fleet-edit.js"></script>

</body>
</html>