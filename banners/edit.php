<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require_role(array('admin'));

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$banner_id = $_GET['id'];

$stmt = mysqli_prepare($connection, 'SELECT * FROM banners WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $banner_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$banner = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$banner) {
    header('Location: index.php');
    exit();
}

$vehicles = mysqli_query($connection, "SELECT id, make, model FROM vehicles ORDER BY make ASC");

$breadcrumbs = array(
    home_breadcrumb(),
    array('label' => 'Manage Banners', 'url' => '../banners/index.php'),
    array('label' => 'Edit Banner', 'url' => null),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - Edit Banner</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
</head>
<body>
    <?php require '../includes/partials/site_header.php'; ?>
    <?php require '../includes/partials/breadcrumbs.php'; ?>

    <div class="admin-form-container">
        <div class="admin-form-card">
            <h1>Edit Banner</h1>
            <p class="admin-page-subtitle">Update this banner's image, title, or settings.</p>

            <?php if (!empty($_SESSION['errors'])) { ?>
                <ul class="admin-form-errors">
                    <?php foreach ($_SESSION['errors'] as $error) { ?>
                        <li><?= e($error) ?></li>
                    <?php } ?>
                </ul>
                <?php unset($_SESSION['errors']); ?>
            <?php } ?>

            <div class="current-photo-preview">
                <img src="../<?= e($banner['image_path']) ?>" alt="<?= e($banner['title']) ?>">
            </div>

            <form action="edit_process.php" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $banner['id'] ?>">

                <label for="title">Banner Title</label>
                <input type="text" id="title" name="title" value="<?= e($banner['title']) ?>">

                <label for="image">Replace Banner Image (optional)</label>
                <input type="file" id="image" name="image" accept="image/*">

                <div class="admin-form-grid">
                    <div>
                        <label for="vehicle_id">Link to Vehicle (optional)</label>
                        <select id="vehicle_id" name="vehicle_id">
                            <option value="">None</option>
                            <?php while ($vehicle = mysqli_fetch_assoc($vehicles)) { ?>
                                <option value="<?= $vehicle['id'] ?>" <?= (int) $vehicle['id'] === (int) $banner['vehicle_id'] ? 'selected' : '' ?>><?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div>
                        <label for="sort_order">Display Order (lower shows first)</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?= (int) $banner['sort_order'] ?>">
                    </div>
                </div>

                <label class="banner-inline-toggle">
                    <input type="checkbox" id="is_active" name="is_active" value="1" <?= $banner['is_active'] ? 'checked' : '' ?>>
                    Active (visible on browse page)
                </label>

                <button type="submit" class="admin-form-submit">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>