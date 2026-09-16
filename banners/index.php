<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require_role(array('admin'));

$result = mysqli_query($connection, "SELECT banners.*, vehicles.make, vehicles.model
    FROM banners
    LEFT JOIN vehicles ON vehicles.id = banners.vehicle_id
    ORDER BY banners.sort_order ASC, banners.created_at DESC");
$banners = mysqli_fetch_all($result, MYSQLI_ASSOC);

$breadcrumbs = array(
    home_breadcrumb(),
    array('label' => 'Manage Banners', 'url' => null),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - Manage Banners</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
</head>
<body>
    <?php require '../includes/partials/site_header.php'; ?>
    <?php require '../includes/partials/breadcrumbs.php'; ?>

    <div class="admin-container">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-page-title">Manage Banners</h1>
                <p class="admin-page-subtitle"><?= count($banners) ?> banner<?= count($banners) === 1 ? '' : 's' ?></p>
            </div>
            <a href="add.php" class="admin-add-btn">+ Add New Banner</a>
        </div>

        <?php if (!empty($_SESSION['success'])) { ?>
            <p><?= e($_SESSION['success']) ?></p>
            <?php unset($_SESSION['success']); ?>
        <?php } ?>

        <?php if (empty($banners)) { ?>
            <div class="admin-card">
                <div class="admin-empty-state">No banners yet. Add one to feature it on the browse page.</div>
            </div>
        <?php } else { ?>
            <div class="banner-admin-grid">
                <?php foreach ($banners as $banner) { ?>
                    <div class="banner-admin-card">
                        <img src="../<?= e($banner['image_path']) ?>" alt="<?= e($banner['title']) ?>">
                        <div class="banner-admin-card-body">
                            <h3><?= e($banner['title'] ?: 'Untitled Banner') ?></h3>
                            <p class="banner-admin-meta">
                                <span class="status-dot <?= $banner['is_active'] ? 'dot-active' : 'dot-inactive' ?>"></span>
                                <?= $banner['is_active'] ? 'Active' : 'Inactive' ?>
                                &middot; Order <?= (int) $banner['sort_order'] ?>
                            </p>
                            <p class="banner-admin-meta">
                                <?php if (!empty($banner['vehicle_id'])) { ?>
                                    Linked to <?= e($banner['make']) ?> <?= e($banner['model']) ?>
                                <?php } else { ?>
                                    Not linked to a vehicle
                                <?php } ?>
                            </p>
                            <div class="banner-admin-actions">
                                <a href="edit.php?id=<?= $banner['id'] ?>" class="admin-action-link">Edit</a>
                                <form action="delete_process.php" method="POST" class="banner-delete-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $banner['id'] ?>">
                                    <button type="submit" class="admin-action-link admin-action-link-plain">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
    <script src="../js/banner-index.js"></script>
</body>
</html>