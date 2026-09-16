<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require_role(array('customer'));

$stmt = mysqli_prepare($connection, "SELECT bookings.*, vehicles.make, vehicles.model, vehicles.image_path
    FROM bookings
    JOIN vehicles ON vehicles.id = bookings.vehicle_id
    WHERE bookings.customer_id = ?
    ORDER BY bookings.created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$bookings = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$addons_by_booking = array();
if (!empty($bookings)) {
    $booking_ids = array_column($bookings, 'id');
    $placeholders = implode(',', array_fill(0, count($booking_ids), '?'));
    $types = str_repeat('i', count($booking_ids));

    $addon_stmt = mysqli_prepare($connection, "SELECT booking_id, addon_name, addon_price FROM booking_addons WHERE booking_id IN ($placeholders)");
    mysqli_stmt_bind_param($addon_stmt, $types, ...$booking_ids);
    mysqli_stmt_execute($addon_stmt);
    $addon_result = mysqli_stmt_get_result($addon_stmt);

    while ($addon_row = mysqli_fetch_assoc($addon_result)) {
        $addons_by_booking[$addon_row['booking_id']][] = $addon_row;
    }

    mysqli_stmt_close($addon_stmt);
}

$breadcrumbs = array(
    home_breadcrumb(),
    array('label' => 'My Bookings', 'url' => null),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - My Bookings</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
</head>
<body>
    <?php require '../includes/partials/site_header.php'; ?>
    <?php require '../includes/partials/breadcrumbs.php'; ?>

    <div class="bookings-page-container">
        <div class="bookings-page-header">
            <h1>My Bookings</h1>
            <p class="admin-page-subtitle"><?= count($bookings) ?> booking<?= count($bookings) === 1 ? '' : 's' ?></p>
        </div>

        <?php if (!empty($_SESSION['success'])) { ?>
            <div class="booking-alert-success"><?= e($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php } ?>

        <?php if (!empty($_SESSION['errors'])) { ?>
            <ul class="booking-alert-error">
                <?php foreach ($_SESSION['errors'] as $error) { ?>
                    <li><?= e($error) ?></li>
                <?php } ?>
            </ul>
            <?php unset($_SESSION['errors']); ?>
        <?php } ?>

        <?php if (empty($bookings)) { ?>
            <div class="bookings-empty">
                <p>You have no bookings yet.</p>
                <a href="../vehicles/browse.php">Browse Vehicles</a>
            </div>
        <?php } else { ?>
            <div class="booking-card-list">
                <?php foreach ($bookings as $booking) { ?>
                    <div class="booking-card">
                        <div class="booking-card-top">
                            <span class="booking-card-vehicle"><?= e($booking['make']) ?> <?= e($booking['model']) ?></span>
                            <span class="status-badge status-<?= e($booking['status']) ?>"><?= e($booking['status']) ?></span>
                        </div>

                        <div class="booking-card-dates">
                            <div>
                                Pickup
                                <strong><?= e($booking['start_date']) ?></strong>
                            </div>
                            <div>
                                Return
                                <strong><?= e($booking['end_date']) ?></strong>
                            </div>
                        </div>

                        <div class="booking-card-fulfillment">
                            <?php if ($booking['fulfillment_type'] === 'delivery') { ?>
                                &#128666; Delivery at <?= e($booking['delivery_time']) ?> to <?= e($booking['delivery_address_line']) ?>, <?= e($booking['delivery_barangay']) ?>, <?= e($booking['delivery_city']) ?>
                            <?php } else { ?>
                                &#128663; Self Pickup (any time on pickup date)
                            <?php } ?>
                        </div>

                        <?php if (!empty($addons_by_booking[$booking['id']])) { ?>
                            <div class="booking-card-addons">
                                Add-ons:
                                <?php
                                    $addon_names = array_map(function ($a) { return $a['addon_name']; }, $addons_by_booking[$booking['id']]);
                                    echo e(implode(', ', $addon_names));
                                ?>
                            </div>
                        <?php } ?>

                        <div class="booking-card-bottom">
                            <span class="booking-card-total">&#8369;<?= number_format($booking['total_price'], 2) ?></span>

                            <?php if (in_array($booking['status'], array('pending', 'confirmed'))) { ?>
                                <form action="cancel.php" method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                    <button type="submit" class="booking-cancel-btn">Cancel Booking</button>
                                </form>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <?php require '../includes/partials/processing_modal.php'; ?>
</body>
</html>