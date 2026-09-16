<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require_role(array('admin'));

if (!isset($_GET['vehicle_id']) || !is_numeric($_GET['vehicle_id'])) {
    header('Location: ../fleet/index.php');
    exit();
}

$vehicle_id = $_GET['vehicle_id'];

$vehicle_stmt = mysqli_prepare($connection, 'SELECT * FROM vehicles WHERE id = ?');
mysqli_stmt_bind_param($vehicle_stmt, 'i', $vehicle_id);
mysqli_stmt_execute($vehicle_stmt);
$vehicle_result = mysqli_stmt_get_result($vehicle_stmt);
$vehicle = mysqli_fetch_assoc($vehicle_result);
mysqli_stmt_close($vehicle_stmt);

if (!$vehicle) {
    header('Location: ../fleet/index.php');
    exit();
}

$bookings_stmt = mysqli_prepare($connection, "SELECT bookings.*, users.name AS customer_name
    FROM bookings
    JOIN users ON users.id = bookings.customer_id
    WHERE bookings.vehicle_id = ?
    ORDER BY bookings.start_date DESC");
mysqli_stmt_bind_param($bookings_stmt, 'i', $vehicle_id);
mysqli_stmt_execute($bookings_stmt);
$bookings_result = mysqli_stmt_get_result($bookings_stmt);
$bookings = mysqli_fetch_all($bookings_result, MYSQLI_ASSOC);
mysqli_stmt_close($bookings_stmt);

$addons_by_booking = array();
$total_lifetime_revenue = 0;
$completed_count = 0;

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

    foreach ($bookings as $b) {
        if ($b['status'] === 'completed') {
            $total_lifetime_revenue += $b['total_price'];
            $completed_count++;
        }
    }
}

$breadcrumbs = array(
    home_breadcrumb(),
    array('label' => 'Manage Fleet', 'url' => '../fleet/index.php'),
    array('label' => $vehicle['make'] . ' ' . $vehicle['model'] . ' History', 'url' => null),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - Vehicle Booking History</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
</head>
<body>
    <?php require '../includes/partials/site_header.php'; ?>
    <?php require '../includes/partials/breadcrumbs.php'; ?>

    <div class="admin-container">
        <div class="vehicle-history-header">
            <?php if (!empty($vehicle['image_path'])) { ?>
                <img src="../<?= e($vehicle['image_path']) ?>" alt="Photo of <?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?>" class="vehicle-history-thumb">
            <?php } ?>
            <div>
                <h1 class="admin-page-title"><?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?></h1>
                <p class="admin-page-subtitle"><?= e($vehicle['plate_number']) ?> &middot; <?= e(ucwords(str_replace('_', ' ', $vehicle['category']))) ?></p>
            </div>
        </div>

        <section class="dashboard-cards">
            <div class="dashboard-card">
                <span class="dashboard-card-label">Total Bookings</span>
                <span class="dashboard-card-value"><?= count($bookings) ?></span>
            </div>
            <div class="dashboard-card">
                <span class="dashboard-card-label">Completed Rentals</span>
                <span class="dashboard-card-value"><?= $completed_count ?></span>
            </div>
            <div class="dashboard-card">
                <span class="dashboard-card-label">Lifetime Revenue</span>
                <span class="dashboard-card-value">&#8369;<?= number_format($total_lifetime_revenue, 2) ?></span>
            </div>
        </section>

        <div class="admin-card">
            <?php if (empty($bookings)) { ?>
                <div class="admin-empty-state">This vehicle has no booking history yet.</div>
            <?php } else { ?>
                <div class="admin-table-scroll">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Pickup</th>
                                <th>Return</th>
                                <th>Fulfillment</th>
                                <th>Add-ons</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Booked On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking) { ?>
                                <tr>
                                    <td><?= e($booking['customer_name']) ?></td>
                                    <td><?= e($booking['start_date']) ?></td>
                                    <td><?= e($booking['end_date']) ?></td>
                                    <td><?= e(ucfirst($booking['fulfillment_type'])) ?></td>
                                    <td>
                                        <?php if (!empty($addons_by_booking[$booking['id']])) { ?>
                                            <?php
                                                $names = array_map(function ($a) { return $a['addon_name']; }, $addons_by_booking[$booking['id']]);
                                                echo e(implode(', ', $names));
                                            ?>
                                        <?php } else { ?>
                                            &mdash;
                                        <?php } ?>
                                    </td>
                                    <td>&#8369;<?= number_format($booking['total_price'], 2) ?></td>
                                    <td><span class="status-badge status-<?= e($booking['status']) ?>"><?= e($booking['status']) ?></span></td>
                                    <td><?= e($booking['created_at']) ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>