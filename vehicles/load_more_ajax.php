<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require '../includes/vehicle_filters.php';

$filters = get_vehicle_filters_from_request();

$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

$vehicles = array();
$pagination = array('total' => 0, 'page' => 1, 'total_pages' => 1);

if (empty($filters['errors'])) {
    $result = fetch_vehicles($connection, $filters, $start_date, $end_date);
    $vehicles = $result['vehicles'];
    $pagination = array(
        'total' => $result['total'],
        'page' => $result['page'],
        'total_pages' => $result['total_pages']
    );
    set_vehicle_browse_loaded_page($filters, $pagination['page']);
}

$favorited_ids = array();
if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
    $fav_stmt = mysqli_prepare($connection, 'SELECT vehicle_id FROM favorites WHERE customer_id = ?');
    mysqli_stmt_bind_param($fav_stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($fav_stmt);
    $fav_result = mysqli_stmt_get_result($fav_stmt);
    while ($row = mysqli_fetch_assoc($fav_result)) {
        $favorited_ids[] = (int) $row['vehicle_id'];
    }
    mysqli_stmt_close($fav_stmt);
}

foreach ($vehicles as $vehicle) {
    $detail_link = '../vehicles/show.php?vehicle_id=' . (int) $vehicle['id'];
    if ($start_date !== null && $end_date !== null) {
        $detail_link .= '&start_date=' . e($start_date) . '&end_date=' . e($end_date);
    }
    $is_favorited = in_array($vehicle['id'], $favorited_ids);
    ?>
    <article class="vehicle-card">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer') { ?>
            <button type="button" class="favorite-btn <?= $is_favorited ? 'favorited' : '' ?>" data-vehicle-id="<?= $vehicle['id'] ?>" aria-label="Toggle favorite">
                <svg viewBox="0 0 24 24" fill="<?= $is_favorited ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.5"><path d="M12 20.5c-.3 0-.6-.1-.8-.3C7.5 17 3 13 3 8.9 3 6.2 5.1 4 7.8 4c1.6 0 3.1.8 4 2 .9-1.2 2.4-2 4-2C18.5 4 20.6 6.2 20.6 8.9c0 4.1-4.5 8.1-8.2 11.3-.2.2-.5.3-.4.3z"/></svg>
            </button>
        <?php } ?>
        <a href="<?= $detail_link ?>">
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
    <?php
}
?>
<template id="load-more-meta" data-has-more="<?= $pagination['page'] < $pagination['total_pages'] ? '1' : '0' ?>" data-next-page="<?= $pagination['page'] + 1 ?>"></template>