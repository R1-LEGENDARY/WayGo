<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require '../includes/vehicle_filters.php';

$filters = get_vehicle_filters_from_request();
$errors = $filters['errors'];
$loaded_browse_page = get_vehicle_browse_loaded_page($filters);

$start_date = null;
$end_date = null;

if (isset($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    $start_timestamp = strtotime($start_date);
    $end_timestamp = strtotime($end_date);

    if ($start_timestamp === false || $end_timestamp === false) {
        $errors[] = 'Please provide valid dates.';
    } elseif ($start_timestamp > $end_timestamp) {
        $errors[] = 'Pickup date must be on or before the return date.';
    }
}

$vehicles = array();
$pagination = array('total' => 0, 'page' => 1, 'total_pages' => 1);

if (empty($errors)) {
    $result = fetch_vehicles($connection, $filters, $start_date, $end_date);
    $vehicles = $result['vehicles'];
    $pagination = array(
        'total' => $result['total'],
        'page' => $result['page'],
        'total_pages' => $result['total_pages']
    );
}

$query_params = $_GET;
unset($query_params['page']);
$query_string = http_build_query($query_params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse and search WayGo's vehicle fleet.">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <title>WayGo - Browse Vehicles</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
</head>
<body>
    <header class="site-header">
        <button type="button" class="burger-btn" id="burger-btn" aria-label="Toggle filters">&#9776;</button>

        <a href="browse.php" class="logo" aria-label="WayGo home">
            <img src="../uploads/waygo_logo.jpg" alt="WayGo" class="brand-logo">
        </a>

        <div class="search-bar">
            <label for="live-search" class="visually-hidden">Search by make or model</label>
            <input type="text" id="live-search" placeholder="Search in WayGo..." value="<?= isset($_GET['search']) ? e($_GET['search']) : '' ?>" autocomplete="off">
            <button type="button" class="search-bar-btn" aria-label="Search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
            </button>
        </div>

        <?php $effective = is_logged_in() ? get_effective_role() : null; ?>

        <?php if ($effective === 'admin') { ?>
            <button type="button" class="burger-btn" id="nav-burger-btn-browse" aria-label="Toggle menu">&#9776;</button>
        <?php } ?>

        <nav class="account-nav">
            <?php if (is_logged_in()) { ?>
                <?php $is_previewing = isset($_SESSION['preview_role']); ?>

                <?php if ($is_previewing) { ?>
                    <span class="preview-badge">Previewing as Customer</span>
                <?php } ?>

                <?php if ($effective === 'customer') { ?>
                    <a href="../favorites/index.php">My Favorites</a>
                    <a href="../booking/my_bookings.php">My Bookings</a>
                <?php } elseif ($effective === 'admin') { ?>
                    <a href="../dashboard/index.php" class="nav-link-desktop-only">Dashboard</a>
                    <a href="../fleet/index.php" class="nav-link-desktop-only">Manage Fleet</a>
                    <a href="../booking/manage.php" class="nav-link-desktop-only">Manage Bookings</a>
                    <a href="../banners/index.php" class="nav-link-desktop-only">Manage Banners</a>
                    <a href="../dashboard/admin_stats.php" class="nav-link-desktop-only">Stats</a>
                <?php } ?>

                <details class="account-details">
                    <summary aria-label="Account menu">
                        <span class="account-icon-btn" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
                        </span>
                    </summary>
                    <div class="account-menu">
                        <span class="account-menu-name"><?= e($_SESSION['name']) ?></span>
                        <span class="account-menu-email"><?= e($_SESSION['email']) ?></span>
                        <?php if ($_SESSION['role'] === 'admin') { ?>
                            <?php if ($is_previewing) { ?>
                                <a href="../account/toggle_preview.php">Exit Preview Mode</a>
                            <?php } else { ?>
                                <a href="../account/toggle_preview.php">Preview as Customer</a>
                            <?php } ?>
                        <?php } ?>
                        <a href="../account/change_password.php">Change Password</a>
                        <a href="../auth/logout.php">Logout</a>
                    </div>
                </details>
            <?php } else { ?>
                <a href="../auth/login.php">Login</a>
                <a href="../auth/register.php">Sign Up</a>
            <?php } ?>
        </nav>
    </header>

    <?php if ($effective === 'admin') { ?>
        <div class="mobile-nav-overlay" id="mobile-nav-overlay-browse"></div>
        <nav class="mobile-nav-panel" id="mobile-nav-panel-browse">
            <div class="mobile-nav-panel-head">
                <img src="../uploads/waygo_logo.jpg" alt="WayGo" class="brand-logo mobile-brand-logo">
                <button type="button" class="sidebar-close-btn" id="mobile-nav-close-btn-browse" aria-label="Close menu">&times;</button>
            </div>
            <div class="mobile-nav-links" id="mobile-nav-links-browse"></div>
        </nav>
    <?php } ?>

    <?php require '../includes/partials/banner_carousel.php'; ?>

    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <h1 class="visually-hidden">Browse rental vehicles</h1>

    <section class="layout-wrapper" aria-label="Vehicle search and results">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-mobile-head">
                <h2>Filters</h2>
                <button type="button" class="sidebar-close-btn" id="sidebar-close-btn" aria-label="Close filters">&times;</button>
            </div>

            <?php if (!empty($errors)) { ?>
                <ul>
                    <?php foreach ($errors as $error) { ?>
                        <li><?= e($error) ?></li>
                    <?php } ?>
                </ul>
            <?php } ?>

            <form action="browse.php" method="GET">
                <?php require '../includes/partials/vehicle_filter_form.php'; ?>
                <button type="submit">Apply Filters</button>
            </form>

            <a href="browse.php" class="sidebar-clear">Clear All Filters</a>
        </aside>

        <main class="results-area">
            <section id="vehicle-results" data-loaded-page="<?= (int) $loaded_browse_page ?>" aria-label="Available vehicles">
                <?php require '../includes/partials/vehicle_results_cards.php'; ?>
            </section>
        </main>
    </section>

    <script src="../js/browse.js"></script>

    <?php require '../includes/partials/scroll_top_btn.php'; ?>
</body>
</html>