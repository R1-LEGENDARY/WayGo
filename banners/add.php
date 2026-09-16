<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require_role(array('admin'));

$vehicles = mysqli_query($connection, "SELECT id, make, model FROM vehicles ORDER BY make ASC");

$breadcrumbs = array(
    home_breadcrumb(),
    array('label' => 'Manage Banners', 'url' => '../banners/index.php'),
    array('label' => 'Add Banner', 'url' => null),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - Add Banner</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
</head>
<body>
    <?php require '../includes/partials/site_header.php'; ?>
    <?php require '../includes/partials/breadcrumbs.php'; ?>

    <div class="admin-form-container">
        <div class="admin-form-card">
            <h1>Add New Banner</h1>
            <p class="admin-page-subtitle">Featured banners appear in the carousel on the browse page.</p>

            <?php if (!empty($_SESSION['errors'])) { ?>
                <ul class="admin-form-errors">
                    <?php foreach ($_SESSION['errors'] as $error) { ?>
                        <li><?= e($error) ?></li>
                    <?php } ?>
                </ul>
                <?php unset($_SESSION['errors']); ?>
            <?php } ?>

            <form action="add_process.php" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <label for="title">Banner Title</label>
                <input type="text" id="title" name="title" placeholder="e.g. Weekend Special - 20% Off SUVs">

                <label for="image">Banner Image</label>
                <input type="file" id="image" name="image" accept="image/*" required>

                <div class="admin-form-grid">
                    <div>
                        <label for="vehicle_id">Link to Vehicle (optional)</label>
                        <select id="vehicle_id" name="vehicle_id">
                            <option value="">None</option>
                            <?php while ($vehicle = mysqli_fetch_assoc($vehicles)) { ?>
                                <option value="<?= $vehicle['id'] ?>"><?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div>
                        <label for="sort_order">Display Order (lower shows first)</label>
                        <input type="number" id="sort_order" name="sort_order" value="0">
                    </div>
                </div>

                <label class="banner-inline-toggle">
                    <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                    Active (visible on browse page)
                </label>

                <button type="submit" class="admin-form-submit">Add Banner</button>
            </form>
        </div>
    </div>

    <?php require '../includes/partials/processing_modal.php'; ?>
</body>
</html>