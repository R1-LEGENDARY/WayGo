<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require_role(array('customer'));

$stmt = mysqli_prepare($connection, "SELECT vehicles.* FROM favorites
    JOIN vehicles ON vehicles.id = favorites.vehicle_id
    WHERE favorites.customer_id = ?
    ORDER BY favorites.created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$vehicles = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$breadcrumbs = array(
    home_breadcrumb(),
    array('label' => 'My Favorites', 'url' => null),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - My Favorites</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
</head>
<body>
    <?php require '../includes/partials/site_header.php'; ?>
    <?php require '../includes/partials/breadcrumbs.php'; ?>

    <div class="bookings-page-container">
        <div class="bookings-page-header">
            <h1>My Favorites</h1>
            <p class="admin-page-subtitle"><?= count($vehicles) ?> vehicle<?= count($vehicles) === 1 ? '' : 's' ?> saved</p>
        </div>

        <?php if (empty($vehicles)) { ?>
            <div class="bookings-empty">
                <p>You haven't saved any vehicles yet.</p>
                <a href="../vehicles/browse.php">Browse Vehicles</a>
            </div>
        <?php } else { ?>
            <div class="vehicle-grid">
                <?php foreach ($vehicles as $vehicle) { ?>
                    <article class="vehicle-card">
                        <div class="vehicle-card-favbtn-wrap">
                            <button type="button" class="favorite-btn favorited" data-vehicle-id="<?= $vehicle['id'] ?>" aria-label="Remove from favorites">
                                <svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5"><path d="M12 20.5c-.3 0-.6-.1-.8-.3C7.5 17 3 13 3 8.9 3 6.2 5.1 4 7.8 4c1.6 0 3.1.8 4 2 .9-1.2 2.4-2 4-2C18.5 4 20.6 6.2 20.6 8.9c0 4.1-4.5 8.1-8.2 11.3-.2.2-.5.3-.4.3z"/></svg>
                            </button>
                        </div>
                        <a href="../vehicles/show.php?vehicle_id=<?= (int) $vehicle['id'] ?>">
                            <?php if (!empty($vehicle['image_path'])) { ?>
                                <img src="../<?= e($vehicle['image_path']) ?>" alt="Photo of <?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?>">
                            <?php } ?>
                            <div class="vehicle-card-body">
                                <h3><?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?></h3>
                                <p class="vehicle-meta"><?= e(ucwords(str_replace('_', ' ', $vehicle['category']))) ?> - <?= e(ucfirst($vehicle['transmission'])) ?></p>
                                <p class="vehicle-price">&#8369;<?= number_format($vehicle['daily_rate'], 2) ?> / day</p>
                            </div>
                        </a>
                    </article>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <script src="../js/favorites.js"></script>
</body>
</html>