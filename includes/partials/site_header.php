<meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

<header class="site-header">
    <button type="button" class="burger-btn" id="nav-burger-btn" aria-label="Toggle menu">&#9776;</button>

        <a href="<?php
        if (is_logged_in() && get_effective_role() === 'admin') {
            echo '../dashboard/index.php';
        } else {
            echo '../vehicles/browse.php';
        }
    ?>" class="logo" aria-label="WayGo home">
            <img src="../uploads/waygo_logo.jpg" alt="WayGo" class="brand-logo">
        </a>

        <nav class="account-nav">
        <?php if (is_logged_in()) { ?>
            <?php
                $effective = get_effective_role();
                $is_previewing = isset($_SESSION['preview_role']);
            ?>

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

<?php require '../includes/partials/toast.php'; ?>

<div class="mobile-nav-overlay" id="mobile-nav-overlay"></div>
<nav class="mobile-nav-panel" id="mobile-nav-panel">
    <div class="mobile-nav-panel-head">
        <img src="../uploads/waygo_logo.jpg" alt="WayGo" class="brand-logo mobile-brand-logo">
        <button type="button" class="sidebar-close-btn" id="mobile-nav-close-btn" aria-label="Close menu">&times;</button>
    </div>
    <div class="mobile-nav-links" id="mobile-nav-links"></div>
</nav>

<script src="../js/site-header.js"></script>