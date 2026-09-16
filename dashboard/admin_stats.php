<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require_role(array('admin'));

$today = date('Y-m-d');

$total_stmt = mysqli_prepare($connection, "SELECT COUNT(*) AS total FROM vehicles WHERE status != 'retired'");
mysqli_stmt_execute($total_stmt);
$total_vehicles = mysqli_fetch_assoc(mysqli_stmt_get_result($total_stmt))['total'];
mysqli_stmt_close($total_stmt);

$active_stmt = mysqli_prepare($connection, "SELECT COUNT(DISTINCT vehicle_id) AS active
    FROM bookings
    WHERE status = 'active'
    AND start_date <= ?
    AND end_date >= ?");
mysqli_stmt_bind_param($active_stmt, 'ss', $today, $today);
mysqli_stmt_execute($active_stmt);
$active_vehicles = mysqli_fetch_assoc(mysqli_stmt_get_result($active_stmt))['active'];
mysqli_stmt_close($active_stmt);

$utilization_rate = $total_vehicles > 0 ? ($active_vehicles / $total_vehicles) * 100 : 0;

$pickups_stmt = mysqli_prepare($connection, "SELECT bookings.id, bookings.start_date, vehicles.make, vehicles.model, vehicles.plate_number, users.name AS customer_name
    FROM bookings
    JOIN vehicles ON vehicles.id = bookings.vehicle_id
    JOIN users ON users.id = bookings.customer_id
    WHERE bookings.status = 'confirmed'
    AND bookings.start_date = ?
    ORDER BY vehicles.make ASC");
mysqli_stmt_bind_param($pickups_stmt, 's', $today);
mysqli_stmt_execute($pickups_stmt);
$pickups_today = mysqli_fetch_all(mysqli_stmt_get_result($pickups_stmt), MYSQLI_ASSOC);
mysqli_stmt_close($pickups_stmt);

$returns_stmt = mysqli_prepare($connection, "SELECT bookings.id, bookings.end_date, vehicles.make, vehicles.model, vehicles.plate_number, users.name AS customer_name
    FROM bookings
    JOIN vehicles ON vehicles.id = bookings.vehicle_id
    JOIN users ON users.id = bookings.customer_id
    WHERE bookings.status = 'active'
    AND bookings.end_date = ?
    ORDER BY vehicles.make ASC");
mysqli_stmt_bind_param($returns_stmt, 's', $today);
mysqli_stmt_execute($returns_stmt);
$returns_today = mysqli_fetch_all(mysqli_stmt_get_result($returns_stmt), MYSQLI_ASSOC);
mysqli_stmt_close($returns_stmt);

$range_start = isset($_GET['range_start']) && !empty($_GET['range_start']) ? $_GET['range_start'] : date('Y-m-01');
$range_end = isset($_GET['range_end']) && !empty($_GET['range_end']) ? $_GET['range_end'] : date('Y-m-d');

$revenue_errors = array();

if (strtotime($range_start) === false || strtotime($range_end) === false) {
    $revenue_errors[] = 'Invalid date range.';
} elseif (strtotime($range_start) > strtotime($range_end)) {
    $revenue_errors[] = 'Range start must be before range end.';
}

$total_revenue = 0;
$completed_count = 0;

if (empty($revenue_errors)) {
    $revenue_stmt = mysqli_prepare($connection, "SELECT COUNT(*) AS completed_count, COALESCE(SUM(total_price), 0) AS total_revenue
        FROM bookings
        WHERE status = 'completed'
        AND end_date BETWEEN ? AND ?");
    mysqli_stmt_bind_param($revenue_stmt, 'ss', $range_start, $range_end);
    mysqli_stmt_execute($revenue_stmt);
    $revenue_row = mysqli_fetch_assoc(mysqli_stmt_get_result($revenue_stmt));
    $total_revenue = $revenue_row['total_revenue'];
    $completed_count = $revenue_row['completed_count'];
    mysqli_stmt_close($revenue_stmt);
}

$breadcrumbs = array(
    home_breadcrumb(),
    array('label' => 'Stats', 'url' => null),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
</head>
<body>
    <?php require '../includes/partials/site_header.php'; ?>
    <?php require '../includes/partials/breadcrumbs.php'; ?>

    <div class="admin-container">
        <h1 class="admin-page-title">Stats & Reports</h1>
        <p class="admin-page-subtitle admin-page-subtitle-spaced">Fleet performance and revenue overview</p>

        <section class="dashboard-cards">
            <div class="dashboard-card">
                <span class="dashboard-card-label">Fleet Utilization</span>
                <span class="dashboard-card-value"><?= number_format($utilization_rate, 1) ?>%</span>
                <span class="admin-page-subtitle"><?= $active_vehicles ?> of <?= $total_vehicles ?> vehicles active</span>
            </div>

            <div class="dashboard-card <?= !empty($pickups_today) ? 'dashboard-card-alert' : '' ?>">
                <span class="dashboard-card-label">Pickups Today</span>
                <span class="dashboard-card-value"><?= count($pickups_today) ?></span>
            </div>

            <div class="dashboard-card">
                <span class="dashboard-card-label">Returns Today</span>
                <span class="dashboard-card-value"><?= count($returns_today) ?></span>
            </div>
        </section>

        <section class="stats-section">
            <h2>Pickups Today</h2>
            <?php if (empty($pickups_today)) { ?>
                <p class="admin-page-subtitle">No pickups scheduled today.</p>
            <?php } else { ?>
                <table class="admin-table">
                    <thead>
                        <tr><th>Customer</th><th>Vehicle</th><th>Plate</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pickups_today as $pickup) { ?>
                            <tr>
                                <td><?= e($pickup['customer_name']) ?></td>
                                <td><?= e($pickup['make']) ?> <?= e($pickup['model']) ?></td>
                                <td><?= e($pickup['plate_number']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </section>

        <section class="stats-section">
            <h2>Returns Today</h2>
            <?php if (empty($returns_today)) { ?>
                <p class="admin-page-subtitle">No returns scheduled today.</p>
            <?php } else { ?>
                <table class="admin-table">
                    <thead>
                        <tr><th>Customer</th><th>Vehicle</th><th>Plate</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($returns_today as $return_item) { ?>
                            <tr>
                                <td><?= e($return_item['customer_name']) ?></td>
                                <td><?= e($return_item['make']) ?> <?= e($return_item['model']) ?></td>
                                <td><?= e($return_item['plate_number']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </section>

        <section class="stats-section">
            <h2>Revenue by Period</h2>

            <?php if (!empty($revenue_errors)) { ?>
                <ul>
                    <?php foreach ($revenue_errors as $error) { ?>
                        <li><?= e($error) ?></li>
                    <?php } ?>
                </ul>
            <?php } ?>

            <form action="admin_stats.php" method="GET" class="stats-filter-form">
                <div>
                    <label for="range_start">From</label>
                    <input type="date" id="range_start" name="range_start" value="<?= e($range_start) ?>">
                </div>
                <div>
                    <label for="range_end">To</label>
                    <input type="date" id="range_end" name="range_end" value="<?= e($range_end) ?>">
                </div>
                <button type="submit">Filter</button>
            </form>

            <p class="admin-page-subtitle"><?= $completed_count ?> completed booking(s)</p>
            <p class="stats-revenue-highlight">&#8369;<?= number_format($total_revenue, 2) ?></p>
        </section>
    </div>
</body>
</html>