<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require_role(array('admin'));

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$allowed_statuses = array('pending', 'confirmed', 'active', 'completed', 'cancelled');

$sql = "SELECT bookings.*, vehicles.make, vehicles.model, vehicles.plate_number, users.name AS customer_name
        FROM bookings
        JOIN vehicles ON vehicles.id = bookings.vehicle_id
        JOIN users ON users.id = bookings.customer_id
        WHERE 1=1";
$types = '';
$params = array();

if (!empty($search)) {
    $sql .= ' AND (users.name LIKE ? OR vehicles.make LIKE ? OR vehicles.model LIKE ?)';
    $term = '%' . $search . '%';
    $types .= 'sss';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if (in_array($filter_status, $allowed_statuses)) {
    $sql .= ' AND bookings.status = ?';
    $types .= 's';
    $params[] = $filter_status;
}

$sql .= ' ORDER BY bookings.start_date ASC';

if (!empty($types)) {
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($connection, $sql);
}

$bookings = mysqli_fetch_all($result, MYSQLI_ASSOC);

function next_actions($current_status) {
    $map = array(
        'pending' => array('confirmed', 'cancelled'),
        'confirmed' => array('active', 'cancelled'),
        'active' => array('completed'),
        'completed' => array(),
        'cancelled' => array(),
    );
    return isset($map[$current_status]) ? $map[$current_status] : array();
}

$breadcrumbs = array(
    home_breadcrumb(),
    array('label' => 'Manage Bookings', 'url' => null),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - Manage Bookings</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
</head>
<body>
    <?php require '../includes/partials/site_header.php'; ?>
    <?php require '../includes/partials/breadcrumbs.php'; ?>

    <div class="admin-container">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-page-title">Manage Bookings</h1>
                <p class="admin-page-subtitle"><?= count($bookings) ?> booking<?= count($bookings) === 1 ? '' : 's' ?> found</p>
            </div>
        </div>

        <?php if (!empty($_SESSION['success'])) { ?>
            <p><?= e($_SESSION['success']) ?></p>
            <?php unset($_SESSION['success']); ?>
        <?php } ?>

        <?php if (!empty($_SESSION['errors'])) { ?>
            <ul>
                <?php foreach ($_SESSION['errors'] as $error) { ?>
                    <li><?= e($error) ?></li>
                <?php } ?>
            </ul>
            <?php unset($_SESSION['errors']); ?>
        <?php } ?>

        <form action="manage.php" method="GET" class="admin-toolbar">
            <div class="admin-search-box">
                <label for="booking-search" class="visually-hidden">Search bookings</label>
                <span class="admin-search-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                </span>
                <input type="text" id="booking-search" name="search" placeholder="Search by customer or vehicle..." value="<?= e($search) ?>">
            </div>

            <label for="booking-status" class="visually-hidden">Filter bookings by status</label>
            <select id="booking-status" name="status" class="admin-filter-select">
                <option value="">All Statuses</option>
                <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= $filter_status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="active" <?= $filter_status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="completed" <?= $filter_status === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $filter_status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>

            <button type="submit">Filter</button>
            <a href="manage.php" class="admin-toolbar-clear">Clear</a>
        </form>

        <div class="admin-card">
            <?php if (empty($bookings)) { ?>
                <div class="admin-empty-state">No bookings match your search or filters.</div>
            <?php } else { ?>
                <div class="admin-table-scroll">
                    <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Plate</th>
                            <th>Pickup</th>
                            <th>Return</th>
                            <th>Fulfillment</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking) { ?>
                            <tr>
                                <td><?= e($booking['customer_name']) ?></td>
                                <td><?= e($booking['make']) ?> <?= e($booking['model']) ?></td>
                                <td><?= e($booking['plate_number']) ?></td>
                                <td><?= e($booking['start_date']) ?></td>
                                <td><?= e($booking['end_date']) ?></td>
                                <td>
                                    <?= e(ucfirst($booking['fulfillment_type'])) ?>
                                    <?php if ($booking['fulfillment_type'] === 'delivery') { ?>
                                        <br><small>
                                            <?= e($booking['delivery_time']) ?><br>
                                            <?= e($booking['delivery_address_line']) ?>, <?= e($booking['delivery_barangay']) ?>,<br>
                                            <?= e($booking['delivery_city']) ?>, <?= e($booking['delivery_province']) ?><br>
                                            Contact: <?= e($booking['contact_number']) ?>
                                        </small>
                                    <?php } ?>
                                </td>
                                <td>&#8369;<?= number_format($booking['total_price'], 2) ?></td>
                                <td><span class="status-badge status-<?= e($booking['status']) ?>"><?= e($booking['status']) ?></span></td>
                                <td class="admin-table-actions">
                                    <?php foreach (next_actions($booking['status']) as $action) { ?>
                                        <form action="update_status.php" method="POST" class="inline-status-form">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <input type="hidden" name="new_status" value="<?= $action ?>">
                                            <button type="submit" class="status-action-btn"><?= ucfirst($action) ?></button>
                                        </form>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php require '../includes/partials/scroll_top_btn.php'; ?>
    <?php require '../includes/partials/processing_modal.php'; ?>
</body>
</html>