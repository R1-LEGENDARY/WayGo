<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/addons.php';
require_role(array('customer'));

if (!isset($_GET['vehicle_id']) || !is_numeric($_GET['vehicle_id'])) {
    header('Location: ../availability/index.php');
    exit();
}

$vehicle_id = $_GET['vehicle_id'];
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$stmt = mysqli_prepare($connection, "SELECT * FROM vehicles WHERE id = ? AND status = 'available'");
mysqli_stmt_bind_param($stmt, 'i', $vehicle_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$vehicle = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$vehicle) {
    header('Location: ../availability/index.php');
    exit();
}

$estimated_vehicle_total = null;
if (!empty($start_date) && !empty($end_date)) {
    $days = (strtotime($end_date) - strtotime($start_date)) / 86400;
    if ($days > 0) {
        $estimated_vehicle_total = $days * $vehicle['daily_rate'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - Confirm Booking</title>
</head>
<body>
    <main>
        <h1>Confirm Booking</h1>

        <?php if (!empty($_SESSION['errors'])) { ?>
            <ul>
                <?php foreach ($_SESSION['errors'] as $error) { ?>
                    <li><?= $error ?></li>
                <?php } ?>
            </ul>
            <?php unset($_SESSION['errors']); ?>
        <?php } ?>

        <h2><?= $vehicle['make'] ?> <?= $vehicle['model'] ?></h2>
        <p>Category: <?= $vehicle['category'] ?> | Transmission: <?= $vehicle['transmission'] ?></p>
        <p>Daily Rate: &#8369;<?= number_format($vehicle['daily_rate'], 2) ?></p>

        <form action="create_process.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?>">

            <label for="start_date">Pickup Date</label>
            <input type="date" id="start_date" name="start_date" value="<?= $start_date ?>" required>

            <label for="end_date">Return Date</label>
            <input type="date" id="end_date" name="end_date" value="<?= $end_date ?>" required>

            <fieldset>
                <legend>Optional Add-ons</legend>

                <?php foreach ($addon_catalog as $addon_key => $addon) { ?>
                    <label for="addon_<?= $addon_key ?>">
                        <input type="checkbox" id="addon_<?= $addon_key ?>" name="addons[]" value="<?= $addon_key ?>">
                        <?= $addon['label'] ?> (+&#8369;<?= number_format($addon['price'], 2) ?>)
                    </label>
                    <br>
                <?php } ?>
            </fieldset>

            <?php if ($estimated_vehicle_total !== null) { ?>
                <p>Estimated Vehicle Cost: &#8369;<?= number_format($estimated_vehicle_total, 2) ?> (add-ons are extra, final total shown after booking)</p>
            <?php } ?>

            <button type="submit">Book Now</button>
        </form>

        <p><a href="../availability/index.php">Back to Search</a></p>
    </main>

    <?php require '../includes/partials/toast.php'; ?>
    <?php require '../includes/partials/processing_modal.php'; ?>
</body>
</html>