<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require_role(array('admin'));

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$allowed_categories = array('sedan', 'suv', 'van', 'pickup_truck', 'motorcycle', 'coupe');
$allowed_statuses = array('available', 'maintenance', 'retired');

$sql = 'SELECT * FROM vehicles WHERE 1=1';
$types = '';
$params = array();

if (!empty($search)) {
    $sql .= ' AND (make LIKE ? OR model LIKE ? OR plate_number LIKE ?)';
    $term = '%' . $search . '%';
    $types .= 'sss';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if (in_array($category_filter, $allowed_categories)) {
    $sql .= ' AND category = ?';
    $types .= 's';
    $params[] = $category_filter;
}

if (in_array($status_filter, $allowed_statuses)) {
    $sql .= ' AND status = ?';
    $types .= 's';
    $params[] = $status_filter;
}

$sql .= ' ORDER BY created_at DESC';

if (!empty($types)) {
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($connection, $sql);
}

$vehicles = mysqli_fetch_all($result, MYSQLI_ASSOC);

$breadcrumbs = array(
    home_breadcrumb(),
    array('label' => 'Manage Fleet', 'url' => null),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - Fleet Management</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
</head>
<body>
    <?php require '../includes/partials/site_header.php'; ?>
    <?php require '../includes/partials/breadcrumbs.php'; ?>

    <div class="admin-container">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-page-title">Fleet Management</h1>
                <p class="admin-page-subtitle"><?= count($vehicles) ?> vehicle<?= count($vehicles) === 1 ? '' : 's' ?> found</p>
            </div>
            <a href="add.php" class="admin-add-btn">+ Add New Vehicle</a>
        </div>

        <?php if (!empty($_SESSION['success'])) { ?>
            <p><?= e($_SESSION['success']) ?></p>
            <?php unset($_SESSION['success']); ?>
        <?php } ?>

        <form action="index.php" method="GET" class="admin-toolbar">
            <div class="admin-search-box">
                <label for="fleet-search" class="visually-hidden">Search fleet</label>
                <span class="admin-search-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                </span>
                <input type="text" id="fleet-search" name="search" placeholder="Search by make, model, or plate number..." value="<?= e($search) ?>">
            </div>

            <label for="fleet-category" class="visually-hidden">Filter by category</label>
            <select id="fleet-category" name="category" class="admin-filter-select">
                <option value="">All Categories</option>
                <option value="sedan" <?= $category_filter === 'sedan' ? 'selected' : '' ?>>Sedan</option>
                <option value="suv" <?= $category_filter === 'suv' ? 'selected' : '' ?>>SUV</option>
                <option value="van" <?= $category_filter === 'van' ? 'selected' : '' ?>>Van</option>
                <option value="pickup_truck" <?= $category_filter === 'pickup_truck' ? 'selected' : '' ?>>Pickup Truck</option>
                <option value="coupe" <?= $category_filter === 'coupe' ? 'selected' : '' ?>>Sports Car / Coupe</option>
                <option value="motorcycle" <?= $category_filter === 'motorcycle' ? 'selected' : '' ?>>Motorcycle</option>
            </select>

            <label for="fleet-status" class="visually-hidden">Filter by status</label>
            <select id="fleet-status" name="status" class="admin-filter-select">
                <option value="">All Statuses</option>
                <option value="available" <?= $status_filter === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="maintenance" <?= $status_filter === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                <option value="retired" <?= $status_filter === 'retired' ? 'selected' : '' ?>>Retired</option>
            </select>

            <button type="submit">Filter</button>
            <a href="index.php" class="admin-toolbar-clear">Clear</a>
        </form>

        <div class="admin-card">
            <?php if (empty($vehicles)) { ?>
                <div class="admin-empty-state">No vehicles match your search or filters.</div>
            <?php } else { ?>
                <div class="admin-table-scroll">
                    <table class="admin-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Make / Model</th>
                            <th>Category</th>
                            <th>Plate</th>
                            <th>Rate</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $vehicle) { ?>
                            <tr>
                                <td>
                                    <?php if (!empty($vehicle['image_path'])) { ?>
                                        <img src="../<?= e($vehicle['image_path']) ?>" alt="Photo of <?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?>" class="admin-thumb">
                                    <?php } ?>
                                </td>
                                <td><strong><?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?></strong></td>
                                <td><?= e(ucwords(str_replace('_', ' ', $vehicle['category']))) ?></td>
                                <td><?= e($vehicle['plate_number']) ?></td>
                                <td>&#8369;<?= number_format($vehicle['daily_rate'], 2) ?></td>
                                <td><span class="status-badge status-<?= e($vehicle['status']) ?>"><?= e($vehicle['status']) ?></span></td>
                                <td class="admin-table-actions">
                                    <a href="edit.php?id=<?= $vehicle['id'] ?>" class="admin-action-link">Edit</a>
                                    <a href="../booking/vehicle_history.php?vehicle_id=<?= $vehicle['id'] ?>" class="admin-action-link">History</a>
                                    <a href="../booking/calendar.php?vehicle_id=<?= $vehicle['id'] ?>&from=fleet" class="admin-action-link">Calendar</a>
                                    <form action="delete_process.php" method="POST" class="inline-status-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $vehicle['id'] ?>">
                                        <button type="button" class="admin-action-link admin-action-link-plain fleet-delete-trigger" data-vehicle-id="<?= $vehicle['id'] ?>">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>

    <dialog id="delete-vehicle-modal">
        <button type="button" class="modal-close-btn" id="close-delete-vehicle-btn" aria-label="Close">&times;</button>
        <div class="modal-icon" aria-hidden="true">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#d9480f" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
        </div>
        <h2>Delete Vehicle</h2>
        <p>Are you sure you want to delete this vehicle? This action cannot be undone.</p>

        <form action="delete_process.php" method="POST" id="delete-vehicle-form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="delete-vehicle-id" value="">
            <button type="submit" class="modal-btn modal-btn-primary">Delete Vehicle</button>
            <button type="button" class="modal-btn modal-btn-secondary" id="cancel-delete-vehicle-btn">Cancel</button>
        </form>
    </dialog>

    <?php require '../includes/partials/scroll_top_btn.php'; ?>

    <script src="../js/fleet-index.js"></script>
</body>
</html>