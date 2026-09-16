<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';

if (!isset($_GET['vehicle_id']) || !is_numeric($_GET['vehicle_id'])) {
    header('Location: ../vehicles/browse.php');
    exit();
}

$vehicle_id = $_GET['vehicle_id'];

$vehicle_stmt = mysqli_prepare($connection, 'SELECT * FROM vehicles WHERE id = ?');
mysqli_stmt_bind_param($vehicle_stmt, 'i', $vehicle_id);
mysqli_stmt_execute($vehicle_stmt);
$vehicle_result = mysqli_stmt_get_result($vehicle_stmt);
$vehicle = mysqli_fetch_assoc($vehicle_result);
mysqli_stmt_close($vehicle_stmt);

if (!$vehicle) {
    header('Location: ../vehicles/browse.php');
    exit();
}

$bookings_stmt = mysqli_prepare($connection, "SELECT start_date, end_date, status FROM bookings
    WHERE vehicle_id = ?
    AND status != 'cancelled'");
mysqli_stmt_bind_param($bookings_stmt, 'i', $vehicle_id);
mysqli_stmt_execute($bookings_stmt);
$bookings_result = mysqli_stmt_get_result($bookings_stmt);
$bookings = mysqli_fetch_all($bookings_result, MYSQLI_ASSOC);
mysqli_stmt_close($bookings_stmt);

$calendar_events = array();
foreach ($bookings as $booking) {
    $exclusive_end = date('Y-m-d', strtotime($booking['end_date'] . ' +1 day'));
    $calendar_events[] = array(
        'start' => $booking['start_date'],
        'end' => $exclusive_end,
        'display' => 'background',
        'color' => $booking['status'] === 'pending' ? '#ffc107' : '#dc3545',
    );
}

$came_from_fleet = isset($_GET['from']) && $_GET['from'] === 'fleet';

if ($came_from_fleet) {
    $breadcrumbs = array(
    home_breadcrumb(),
        array('label' => 'Manage Fleet', 'url' => '../fleet/index.php'),
        array('label' => 'Availability Calendar', 'url' => null),
    );
    $back_link_url = '../fleet/index.php';
    $back_link_label = 'Back to Fleet';
} else {
    $breadcrumbs = array(
    home_breadcrumb(),
        array('label' => $vehicle['make'] . ' ' . $vehicle['model'], 'url' => '../vehicles/show.php?vehicle_id=' . (int) $vehicle['id']),
        array('label' => 'Availability Calendar', 'url' => null),
    );
    $back_link_url = '../vehicles/show.php?vehicle_id=' . (int) $vehicle['id'];
    $back_link_label = 'Back to Vehicle Details';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - <?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?> Calendar</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.js"></script>
</head>
<body>
    <?php require '../includes/partials/site_header.php'; ?>
    <?php require '../includes/partials/breadcrumbs.php'; ?>

    <div class="calendar-page-container">
        <div class="calendar-page-header">
            <h1><?= e($vehicle['make']) ?> <?= e($vehicle['model']) ?> Availability</h1>
            <div class="calendar-legend">
                <span><span class="calendar-legend-dot dot-booked"></span> Booked</span>
                <span><span class="calendar-legend-dot dot-pending"></span> Pending</span>
                <span><span class="calendar-legend-dot dot-open"></span> Open</span>
            </div>
        </div>

        <div class="calendar-card">
            <div id="vehicle-calendar" data-events="<?= e(json_encode($calendar_events)) ?>"></div>
        </div>
    </div>

    <script src="../js/booking-calendar.js"></script>
</body>
</html>