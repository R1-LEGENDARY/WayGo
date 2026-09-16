<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require '../includes/addons.php';

if (!isset($_GET['vehicle_id']) || !is_numeric($_GET['vehicle_id'])) {
    header('Location: browse.php');
    exit();
}

$vehicle_id = $_GET['vehicle_id'];
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$stmt = mysqli_prepare($connection, 'SELECT * FROM vehicles WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $vehicle_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$vehicle = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$vehicle) {
    header('Location: browse.php');
    exit();
}

$gallery_stmt = mysqli_prepare($connection, 'SELECT image_path FROM vehicle_images WHERE vehicle_id = ? ORDER BY sort_order ASC, id ASC');
mysqli_stmt_bind_param($gallery_stmt, 'i', $vehicle_id);
mysqli_stmt_execute($gallery_stmt);
$gallery_result = mysqli_stmt_get_result($gallery_stmt);
$gallery_images = mysqli_fetch_all($gallery_result, MYSQLI_ASSOC);
mysqli_stmt_close($gallery_stmt);

if (empty($gallery_images) && !empty($vehicle['image_path'])) {
    $gallery_images = array(array('image_path' => $vehicle['image_path']));
}

$estimated_vehicle_total = null;
if (!empty($start_date) && !empty($end_date)) {
    $days = (strtotime($end_date) - strtotime($start_date)) / 86400;
    if ($days > 0) {
        $estimated_vehicle_total = $days * $vehicle['daily_rate'];
    }
}

$has_location = !empty($vehicle['latitude']) && !empty($vehicle['longitude']);

$return_params = array('vehicle_id' => $vehicle_id);
if (!empty($start_date)) $return_params['start_date'] = $start_date;
if (!empty($end_date)) $return_params['end_date'] = $end_date;
$return_url = '../vehicles/show.php?' . http_build_query($return_params);

$breadcrumbs = array(
    home_breadcrumb(),
    array('label' => $vehicle['make'] . ' ' . $vehicle['model'], 'url' => null),
);

$booking_errors = array();
if (!empty($_SESSION['errors'])) {
    $booking_errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View details and book <?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?> on WayGo.">
    <title>WayGo - <?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?></title>
    <link rel="stylesheet" href="../css/style.css?v=7">
    <?php if ($has_location) { ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php } ?>
</head>
<body>
    <?php require '../includes/partials/site_header.php'; ?>
    <?php require '../includes/partials/breadcrumbs.php'; ?>

    <main class="vehicle-detail-layout">
        <section class="vehicle-gallery-col" aria-label="Vehicle photos">
            <?php if (!empty($gallery_images)) { ?>
                <div class="gallery" id="gallery">
                    <div class="gallery-main">
                        <button type="button" class="gallery-arrow gallery-prev" id="gallery-prev" aria-label="Previous photo">&#8249;</button>
                        <div class="gallery-track" id="gallery-track">
                            <?php foreach ($gallery_images as $img) { ?>
                                <div class="gallery-slide">
                                    <img src="../<?= e($img['image_path']) ?>" alt="" class="gallery-slide-bg">
                                    <img src="../<?= e($img['image_path']) ?>" alt="Photo of <?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?>" class="gallery-slide-fg">
                                </div>
                            <?php } ?>
                        </div>
                        <button type="button" class="gallery-arrow gallery-next" id="gallery-next" aria-label="Next photo">&#8250;</button>
                        <span class="gallery-counter" id="gallery-counter">1 / <?= count($gallery_images) ?></span>
                    </div>

                    <?php if (count($gallery_images) > 1) { ?>
                        <div class="gallery-thumbs" id="gallery-thumbs">
                            <?php foreach ($gallery_images as $index => $img) { ?>
                                <button type="button" class="gallery-thumb <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>">
                                    <img src="../<?= e($img['image_path']) ?>" alt="Thumbnail <?= $index + 1 ?>">
                                </button>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="gallery-empty">No photos available</div>
            <?php } ?>
        </section>

        <section class="vehicle-info-col" aria-label="Vehicle details">
            <h1><?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?></h1>

            <ul class="vehicle-spec-list">
                <li>Category: <?= e(ucwords(str_replace('_', ' ', $vehicle['category']))) ?></li>
                <?php if ($vehicle['category'] === 'motorcycle' && !empty($vehicle['motorcycle_type'])) { ?>
                    <li>Type: <?= e(ucwords(str_replace('_', ' ', $vehicle['motorcycle_type']))) ?></li>
                <?php } ?>
                <?php if ($vehicle['is_sports']) { ?>
                    <li>Sports variant</li>
                <?php } ?>
                <li>Transmission: <?= e(ucfirst($vehicle['transmission'])) ?></li>
                <li>Status: <?= e(ucfirst($vehicle['status'])) ?></li>
            </ul>

            <p class="vehicle-detail-price">&#8369;<?= number_format($vehicle['daily_rate'], 2) ?> <span>/ day</span></p>

            <p><a href="../booking/calendar.php?vehicle_id=<?= (int) $vehicle['id'] ?>">View Availability Calendar</a></p>

            <section class="location-section">
                <h2>Pickup Location</h2>

                <?php if (!empty($vehicle['location_name'])) { ?>
                    <p class="pickup-location-address"><?= e($vehicle['location_name']) ?></p>
                <?php } ?>

                <?php if ($has_location) { ?>
                    <div id="pickup-map" class="pickup-map"
                        data-lat="<?= e((float) $vehicle['latitude']) ?>"
                        data-lng="<?= e((float) $vehicle['longitude']) ?>"
                        data-popup-text="<?= e($vehicle['make'] . ' ' . $vehicle['model']) ?>"></div>
                <?php } else { ?>
                    <p>Pickup location not yet set for this vehicle.</p>
                <?php } ?>
            </section>

            <?php if ($vehicle['status'] !== 'available') { ?>
                <p class="not-bookable">This vehicle is not currently bookable.</p>
            <?php } elseif (!is_logged_in()) { ?>
                <button type="button" id="open-auth-modal-btn" class="booking-trigger-btn">
                    <svg class="icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17h14M5 17a2 2 0 100 4 2 2 0 000-4zm14 0a2 2 0 100 4 2 2 0 000-4zM5 17V9l2-5h10l2 5v8"/></svg>
                    Book This Vehicle
                </button>

                <dialog id="auth-modal">
                    <button type="button" id="close-auth-modal-btn" class="modal-close-btn" aria-label="Close">&times;</button>
                    <div class="modal-icon" aria-hidden="true">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#d9480f" stroke-width="1.5"><path d="M5 17h14M5 17a2 2 0 100 4 2 2 0 000-4zm14 0a2 2 0 100 4 2 2 0 000-4zM5 17V9l2-5h10l2 5v8"/></svg>
                    </div>
                    <h2>Ready to book this ride?</h2>
                    <p>Log in or create a free account to continue your booking.</p>
                    <a href="../auth/login.php?redirect=<?= urlencode($return_url) ?>" class="modal-btn modal-btn-primary">Login</a>
                    <a href="../auth/register.php?redirect=<?= urlencode($return_url) ?>" class="modal-btn modal-btn-secondary">Create an Account</a>
                </dialog>
            <?php } elseif ($_SESSION['role'] === 'customer') { ?>
                <button type="button" id="open-booking-modal-btn" class="booking-trigger-btn">
                    <svg class="icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17h14M5 17a2 2 0 100 4 2 2 0 000-4zm14 0a2 2 0 100 4 2 2 0 000-4zM5 17V9l2-5h10l2 5v8"/></svg>
                    Book This Vehicle
                </button>

                <dialog id="booking-modal">
                    <div class="booking-modal-header">
                        <h2>Book This Vehicle</h2>
                        <button type="button" id="close-booking-modal-btn" class="booking-modal-close-btn" aria-label="Close">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1f2933" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="booking-modal-body">
                        <?php 
                            $field_errors = array();
                            if (!empty($_SESSION['field_errors'])) {
                                $field_errors = $_SESSION['field_errors'];
                                unset($_SESSION['field_errors']);
                            }
                        ?>

                        <form action="../booking/create_process.php" method="POST" id="booking-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="vehicle_id" value="<?= (int) $vehicle['id'] ?>">

                            <p class="booking-step-label">1. Choose Your Dates</p>
                            <div class="date-input-row">
                                <div>
                                    <label for="start_date">Pickup Date</label>
                                    <input type="date" id="start_date" name="start_date" value="<?= e($start_date) ?>" required>
                                    <?php if (isset($field_errors['dates'])) { ?>
                                        <span class="field-error"><?= e($field_errors['dates']) ?></span>
                                    <?php } ?>
                                </div>
                                <div>
                                    <label for="end_date">Return Date</label>
                                    <input type="date" id="end_date" name="end_date" value="<?= e($end_date) ?>" required>
                                </div>
                            </div>

                            <p class="booking-step-label">2. How Will You Get It?</p>
                            <div class="fulfillment-options">
                                <label class="fulfillment-card selected" id="pickup-card">
                                    <input type="radio" name="fulfillment_type" value="pickup" checked>
                                    <span class="fulfillment-card-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17h14M5 17a2 2 0 100 4 2 2 0 000-4zm14 0a2 2 0 100 4 2 2 0 000-4zM5 17V9l2-5h10l2 5v8"/></svg>
                                    </span>
                                    <span class="fulfillment-card-title">Pickup</span>
                                    <span class="fulfillment-card-desc">Anytime on your pickup date</span>
                                </label>
                                <label class="fulfillment-card" id="delivery-card">
                                    <input type="radio" name="fulfillment_type" value="delivery">
                                    <span class="fulfillment-card-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="13" height="10" rx="1"/><path d="M16 10h3l3 3v4h-6z"/><circle cx="7.5" cy="19" r="1.5"/><circle cx="17.5" cy="19" r="1.5"/></svg>
                                    </span>
                                    <span class="fulfillment-card-title">Delivery</span>
                                    <span class="fulfillment-card-desc">We bring it to you</span>
                                </label>
                            </div>

                            <div class="delivery-detail-box hidden-field" id="delivery-fields">
                                <p class="delivery-subheading">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                    When & How to Reach You
                                </p>

                                <div class="field-row">
                                    <div>
                                        <label for="delivery_time">Delivery Time</label>
                                        <div class="time-input-row">
                                            <input type="time" id="delivery_time" name="delivery_time" class="<?= isset($field_errors['delivery_time']) ? 'has-error' : '' ?>">
                                            <button type="button" id="confirm-time-btn" class="time-confirm-btn">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                                                Set
                                            </button>
                                        </div>
                                        <?php if (isset($field_errors['delivery_time'])) { ?>
                                            <span class="field-error"><?= e($field_errors['delivery_time']) ?></span>
                                        <?php } ?>
                                        <p class="time-confirmed-note" id="time-confirmed-note">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                                            Time confirmed
                                        </p>
                                    </div>
                                    <div>
                                        <label for="contact_number">Contact Number</label>
                                        <input type="text" id="contact_number" name="contact_number" placeholder="09XX XXX XXXX" class="<?= isset($field_errors['contact_number']) ? 'has-error' : '' ?>">
                                        <?php if (isset($field_errors['contact_number'])) { ?>
                                            <span class="field-error"><?= e($field_errors['contact_number']) ?></span>
                                        <?php } ?>
                                    </div>
                                </div>

                                <p class="delivery-subheading">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                    Delivery Address
                                </p>

                                <div class="field-full">
                                    <label for="delivery_region">Region</label>
                                    <select id="delivery_region" name="delivery_region" class="<?= isset($field_errors['delivery_region']) ? 'has-error' : '' ?>"><option value="">Loading regions...</option></select>
                                    <?php if (isset($field_errors['delivery_region'])) { ?>
                                        <span class="field-error"><?= e($field_errors['delivery_region']) ?></span>
                                    <?php } ?>
                                </div>

                                <div class="field-row">
                                    <div>
                                        <label for="delivery_province">Province</label>
                                        <select id="delivery_province" name="delivery_province" disabled class="<?= isset($field_errors['delivery_province']) ? 'has-error' : '' ?>"><option value="">Select region first</option></select>
                                        <?php if (isset($field_errors['delivery_province'])) { ?>
                                            <span class="field-error"><?= e($field_errors['delivery_province']) ?></span>
                                        <?php } ?>
                                    </div>
                                    <div>
                                        <label for="delivery_city">City / Municipality</label>
                                        <select id="delivery_city" name="delivery_city" disabled class="<?= isset($field_errors['delivery_city']) ? 'has-error' : '' ?>"><option value="">Select province first</option></select>
                                        <?php if (isset($field_errors['delivery_city'])) { ?>
                                            <span class="field-error"><?= e($field_errors['delivery_city']) ?></span>
                                        <?php } ?>
                                    </div>
                                </div>

                                <div class="field-full">
                                    <label for="delivery_barangay">Barangay</label>
                                    <select id="delivery_barangay" name="delivery_barangay" disabled class="<?= isset($field_errors['delivery_barangay']) ? 'has-error' : '' ?>"><option value="">Select city first</option></select>
                                    <?php if (isset($field_errors['delivery_barangay'])) { ?>
                                        <span class="field-error"><?= e($field_errors['delivery_barangay']) ?></span>
                                    <?php } ?>
                                </div>

                                <div class="field-full">
                                    <label for="delivery_address_line">Street / House No. / Landmark</label>
                                    <input type="text" id="delivery_address_line" name="delivery_address_line" placeholder="e.g. 123 Rizal St., near the plaza" class="<?= isset($field_errors['delivery_address_line']) ? 'has-error' : '' ?>">
                                    <?php if (isset($field_errors['delivery_address_line'])) { ?>
                                        <span class="field-error"><?= e($field_errors['delivery_address_line']) ?></span>
                                    <?php } ?>
                                </div>
                            </div>

                            <p class="booking-step-label">3. Optional Add-ons</p>
                            <div class="addon-options">
                                <?php foreach ($addon_catalog as $addon_key => $addon) { ?>
                                    <label class="addon-card">
                                        <input type="checkbox" name="addons[]" value="<?= e($addon_key) ?>">
                                        <?= e($addon['label']) ?>
                                        <span class="addon-card-price">+&#8369;<?= number_format($addon['price'], 2) ?></span>
                                    </label>
                                <?php } ?>
                            </div>

                            <?php if ($estimated_vehicle_total !== null) { ?>
                                <div class="booking-estimate">
                                    <span>Estimated Vehicle Cost</span>
                                    <strong>&#8369;<?= number_format($estimated_vehicle_total, 2) ?></strong>
                                </div>
                            <?php } ?>

                            <button type="submit" class="book-now-btn">Confirm Booking</button>
                        </form>
                    </div>
                </dialog>
            <?php } else { ?>
                <p class="not-bookable">Only customer accounts can book vehicles.</p>
            <?php } ?>
        </section>
    </main>

    <?php require '../includes/partials/processing_modal.php'; ?>

    <script src="../js/vehicle-detail.js"></script>
    
</body>
</html>