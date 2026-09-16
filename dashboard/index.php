<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require_role(array('admin'));

$total_stmt = mysqli_prepare($connection, "SELECT COUNT(*) AS total FROM vehicles WHERE status != 'retired'");
mysqli_stmt_execute($total_stmt);
$total_vehicles = mysqli_fetch_assoc(mysqli_stmt_get_result($total_stmt))['total'];
mysqli_stmt_close($total_stmt);

$available_stmt = mysqli_prepare($connection, "SELECT COUNT(*) AS total FROM vehicles WHERE status = 'available'");
mysqli_stmt_execute($available_stmt);
$available_vehicles = mysqli_fetch_assoc(mysqli_stmt_get_result($available_stmt))['total'];
mysqli_stmt_close($available_stmt);

$pending_stmt = mysqli_prepare($connection, "SELECT COUNT(*) AS total FROM bookings WHERE status = 'pending'");
mysqli_stmt_execute($pending_stmt);
$pending_count = mysqli_fetch_assoc(mysqli_stmt_get_result($pending_stmt))['total'];
mysqli_stmt_close($pending_stmt);

$active_stmt = mysqli_prepare($connection, "SELECT COUNT(*) AS total FROM bookings WHERE status = 'active'");
mysqli_stmt_execute($active_stmt);
$active_count = mysqli_fetch_assoc(mysqli_stmt_get_result($active_stmt))['total'];
mysqli_stmt_close($active_stmt);

$month_start = date('Y-m-01');
$month_end = date('Y-m-d');
$revenue_stmt = mysqli_prepare($connection, "SELECT COALESCE(SUM(total_price), 0) AS total FROM bookings WHERE status = 'completed' AND end_date BETWEEN ? AND ?");
mysqli_stmt_bind_param($revenue_stmt, 'ss', $month_start, $month_end);
mysqli_stmt_execute($revenue_stmt);
$month_revenue = mysqli_fetch_assoc(mysqli_stmt_get_result($revenue_stmt))['total'];
mysqli_stmt_close($revenue_stmt);

$trend_range_start = date('Y-m-d', strtotime('-30 days'));
$trend_range_end = date('Y-m-d');

$trend_stmt = mysqli_prepare($connection, "SELECT end_date, SUM(total_price) AS daily_total
    FROM bookings
    WHERE status = 'completed'
    AND end_date BETWEEN ? AND ?
    GROUP BY end_date
    ORDER BY end_date ASC");
mysqli_stmt_bind_param($trend_stmt, 'ss', $trend_range_start, $trend_range_end);
mysqli_stmt_execute($trend_stmt);
$trend_data = mysqli_fetch_all(mysqli_stmt_get_result($trend_stmt), MYSQLI_ASSOC);
mysqli_stmt_close($trend_stmt);

$top_vehicles_stmt = mysqli_prepare($connection, "SELECT vehicles.id, vehicles.make, vehicles.model, vehicles.image_path,
    COUNT(bookings.id) AS booking_count, COALESCE(SUM(bookings.total_price), 0) AS vehicle_revenue
    FROM bookings
    JOIN vehicles ON vehicles.id = bookings.vehicle_id
    WHERE bookings.status = 'completed'
    GROUP BY vehicles.id
    ORDER BY booking_count DESC, vehicle_revenue DESC
    LIMIT 5");
mysqli_stmt_execute($top_vehicles_stmt);
$top_vehicles = mysqli_fetch_all(mysqli_stmt_get_result($top_vehicles_stmt), MYSQLI_ASSOC);
mysqli_stmt_close($top_vehicles_stmt);

$upcoming_stmt = mysqli_prepare($connection, "SELECT bookings.id, bookings.start_date, bookings.fulfillment_type,
    bookings.delivery_time, bookings.delivery_address_line, bookings.delivery_barangay, bookings.delivery_city,
    bookings.contact_number, vehicles.make, vehicles.model, vehicles.location_name, users.name AS customer_name
    FROM bookings
    JOIN vehicles ON vehicles.id = bookings.vehicle_id
    JOIN users ON users.id = bookings.customer_id
    WHERE bookings.status = 'confirmed'
    AND bookings.start_date >= CURDATE()
    ORDER BY bookings.start_date ASC");
mysqli_stmt_execute($upcoming_stmt);
$upcoming_fulfillments = mysqli_fetch_all(mysqli_stmt_get_result($upcoming_stmt), MYSQLI_ASSOC);
mysqli_stmt_close($upcoming_stmt);

$breadcrumbs = array(
    array('label' => 'Dashboard', 'url' => null),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php require '../includes/partials/site_header.php'; ?>
    <?php require '../includes/partials/breadcrumbs.php'; ?>

    <main class="dashboard-main">
        <h1>Welcome back, <?= e($_SESSION['name']) ?></h1>

        <section class="dashboard-cards">
            <div class="dashboard-card <?= $pending_count > 0 ? 'dashboard-card-alert' : '' ?>">
                <span class="dashboard-card-label">Pending Approvals</span>
                <span class="dashboard-card-value"><?= $pending_count ?></span>
                <a href="../booking/manage.php?status=pending">Review now &raquo;</a>
            </div>

            <div class="dashboard-card">
                <span class="dashboard-card-label">Active Rentals</span>
                <span class="dashboard-card-value"><?= $active_count ?></span>
                <a href="../booking/manage.php?status=active">View &raquo;</a>
            </div>

            <div class="dashboard-card">
                <span class="dashboard-card-label">Vehicles Available</span>
                <span class="dashboard-card-value"><?= $available_vehicles ?> / <?= $total_vehicles ?></span>
                <a href="../fleet/index.php">Manage fleet &raquo;</a>
            </div>

            <div class="dashboard-card">
                <span class="dashboard-card-label">Revenue This Month</span>
                <span class="dashboard-card-value">&#8369;<?= number_format($month_revenue, 2) ?></span>
                <a href="../dashboard/admin_stats.php">Full report &raquo;</a>
            </div>
        </section>

        <div class="dashboard-analytics-grid">
            <section class="stats-section">
                <h2>Revenue Trend (Last 30 Days)</h2>
                <div class="revenue-chart-wrapper">
                    <canvas id="revenue-chart"
                        data-labels="<?= e(json_encode(array_map(function ($r) { return date('M j', strtotime($r['end_date'])); }, $trend_data))) ?>"
                        data-values="<?= e(json_encode(array_map(function ($r) { return (float) $r['daily_total']; }, $trend_data))) ?>"></canvas>
                </div>
            </section>

            <div class="analytics-side-col">
                <section class="stats-section">
                    <h2>Top Performing Vehicles</h2>
                    <p class="admin-page-subtitle">By completed bookings</p>

                    <?php if (empty($top_vehicles)) { ?>
                        <p class="admin-page-subtitle">No completed bookings yet.</p>
                    <?php } else { ?>
                        <div class="top-vehicle-list">
                            <?php foreach ($top_vehicles as $index => $tv) { ?>
                                <div class="top-vehicle-row">
                                    <span class="top-vehicle-rank">#<?= $index + 1 ?></span>
                                    <?php if (!empty($tv['image_path'])) { ?>
                                        <img src="../<?= e($tv['image_path']) ?>" alt="Photo of <?= e($tv['make']) ?> <?= e($tv['model']) ?>" class="top-vehicle-thumb">
                                    <?php } ?>
                                    <div class="top-vehicle-info">
                                        <span class="top-vehicle-name"><?= e($tv['make']) ?> <?= e($tv['model']) ?></span>
                                        <span class="top-vehicle-meta"><?= $tv['booking_count'] ?> booking<?= $tv['booking_count'] == 1 ? '' : 's' ?></span>
                                    </div>
                                    <span class="top-vehicle-revenue">&#8369;<?= number_format($tv['vehicle_revenue'], 2) ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </section>

                <section class="stats-section">
                    <h2>Upcoming</h2>

                    <?php if (empty($upcoming_fulfillments)) { ?>
                        <p class="admin-page-subtitle">Nothing scheduled.</p>
                    <?php } else { ?>
                        <div class="upcoming-list">
                            <?php foreach ($upcoming_fulfillments as $item) { ?>
                                <div class="upcoming-row">
                                    <span class="upcoming-date-badge"><?= date('M j', strtotime($item['start_date'])) ?></span>
                                    <div class="upcoming-info">
                                        <span class="upcoming-title">
                                            <?= e($item['customer_name']) ?> &middot; <?= e($item['make']) ?> <?= e($item['model']) ?>
                                        </span>
                                        <span class="upcoming-detail">
                                            <?php if ($item['fulfillment_type'] === 'delivery') { ?>
                                                &#128666; Delivery <?php if (!empty($item['delivery_time'])) { ?> at <?= e($item['delivery_time']) ?><?php } ?>
                                                &middot; <?= e($item['delivery_barangay']) ?>, <?= e($item['delivery_city']) ?>
                                            <?php } else { ?>
                                                &#128663; Pickup at <?= e($item['location_name'] ?: 'main garage') ?>
                                            <?php } ?>
                                        </span>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </section>
            </div>
        </div>

    </main>

    <script src="../js/dashboard-chart.js"></script>
</body>
</html>