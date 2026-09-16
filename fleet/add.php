<?php
require '../includes/auth.php';
require '../includes/helpers.php';
require_role(array('admin'));

$breadcrumbs = array(
    home_breadcrumb(),
    array('label' => 'Manage Fleet', 'url' => '../fleet/index.php'),
    array('label' => 'Add Vehicle', 'url' => null),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - Add Vehicle</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <?php require '../includes/partials/site_header.php'; ?>
    <?php require '../includes/partials/breadcrumbs.php'; ?>

    <div class="admin-form-container">
        <div class="admin-form-card">
            <h1>Add New Vehicle</h1>
            <p class="admin-page-subtitle">Fill in the details below to add a vehicle to the fleet.</p>

            <?php if (!empty($_SESSION['errors'])) { ?>
                <ul class="admin-form-errors">
                    <?php foreach ($_SESSION['errors'] as $error) { ?>
                        <li><?= $error ?></li>
                    <?php } ?>
                </ul>
                <?php unset($_SESSION['errors']); ?>
            <?php } ?>

            <form action="add_process.php" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="admin-form-grid">
                    <div>
                        <label for="make">Make</label>
                        <input type="text" id="make" name="make" required>
                    </div>
                    <div>
                        <label for="model">Model</label>
                        <input type="text" id="model" name="model" required>
                    </div>

                    <div>
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="sedan">Sedan</option>
                            <option value="suv">SUV</option>
                            <option value="van">Van</option>
                            <option value="pickup_truck">Pickup Truck</option>
                            <option value="coupe">Sports Car / Coupe</option>
                            <option value="motorcycle">Motorcycle</option>
                        </select>
                    </div>
                    <div>
                        <label for="plate_number">Plate Number</label>
                        <input type="text" id="plate_number" name="plate_number" required>
                    </div>

                    <div id="motorcycle-type-field" class="hidden-field">
                        <label for="motorcycle_type">Motorcycle Type</label>
                        <select id="motorcycle_type" name="motorcycle_type">
                            <option value="scooter">Scooter (Automatic)</option>
                            <option value="underbone">Underbone (Manual Clutch)</option>
                            <option value="sport_bike">Sport Bike (Manual Clutch)</option>
                            <option value="naked">Naked / Standard (Manual Clutch)</option>
                            <option value="cruiser">Cruiser (Manual Clutch)</option>
                        </select>
                    </div>

                    <div id="transmission-field">
                        <label for="transmission">Transmission</label>
                        <select id="transmission" name="transmission" required>
                            <option value="automatic">Automatic</option>
                            <option value="manual">Manual</option>
                        </select>
                    </div>

                    <div>
                        <label for="daily_rate">Daily Rate</label>
                        <input type="number" id="daily_rate" name="daily_rate" step="0.01" min="0" required>
                    </div>
                    <div>
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="available">Available</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="retired">Retired</option>
                        </select>
                    </div>

                    <div class="full-width hidden-field" id="sports-field">
                        <label>
                            <input type="checkbox" id="is_sports" name="is_sports" value="1">
                            Sports variant (e.g. sports car, high-performance trim)
                        </label>
                    </div>

                    <div class="full-width">
                        <label for="images">Vehicle Photos (first photo becomes the main thumbnail; select several)</label>
                        <input type="file" id="images" name="images[]" accept="image/*" multiple>
                    </div>
                </div>

                <fieldset>
                    <legend>Pickup Location</legend>

                    <label for="location_name">Location Name / Address (auto-filled)</label>
                    <input type="text" id="location_name" name="location_name" placeholder="Pin a location or use GPS to fill this automatically">

                    <div class="locate-btn-row">
                        <button type="button" id="locate-btn">Use My Current Location</button>
                        <span class="locate-status-text" id="locate-status"></span>
                    </div>
                    <p class="location-hint">Use GPS or click the map to automatically fill the address and set the exact pickup point.</p>

                    <div class="location-map-box" id="location-map"></div>

                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                </fieldset>

                <button type="submit" class="admin-form-submit">Add Vehicle</button>
            </form>
        </div>
    </div>

    <script src="../js/fleet.js"></script>

    <?php require '../includes/partials/processing_modal.php'; ?>
</body>
</html>