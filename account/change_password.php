<?php
require '../includes/auth.php';
require '../includes/helpers.php';
require_login();

$breadcrumbs = array(
    home_breadcrumb(),
    array('label' => 'Change Password', 'url' => null),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - Change Password</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
</head>
<body>
    <?php require '../includes/partials/site_header.php'; ?>
    <?php require '../includes/partials/breadcrumbs.php'; ?>

    <div class="admin-form-container">
        <div class="admin-form-card">
            <h1>Change Password</h1>
            <p class="admin-page-subtitle">Choose a strong new password for your account.</p>

            <?php if (!empty($_SESSION['success'])) { ?>
                <div class="admin-form-success"><?= e($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php } ?>

            <?php if (!empty($_SESSION['errors'])) { ?>
                <ul class="admin-form-errors">
                    <?php foreach ($_SESSION['errors'] as $error) { ?>
                        <li><?= e($error) ?></li>
                    <?php } ?>
                </ul>
                <?php unset($_SESSION['errors']); ?>
            <?php } ?>

            <form action="change_password_process.php" method="POST">
                <?= csrf_field() ?>
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>

                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>

                <label for="confirm_new_password">Confirm New Password</label>
                <input type="password" id="confirm_new_password" name="confirm_new_password" required>

                <button type="submit" class="admin-form-submit">Update Password</button>
            </form>
        </div>
    </div>

    <?php require '../includes/partials/processing_modal.php'; ?>
</body>
</html>