<?php
require '../includes/auth.php';
require '../includes/helpers.php';

$field_errors = isset($_SESSION['field_errors']) ? $_SESSION['field_errors'] : array();
$old_input = isset($_SESSION['old_input']) ? $_SESSION['old_input'] : array();
unset($_SESSION['field_errors'], $_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayGo - Login</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
</head>
<body>
    <div class="auth-page">
        <div class="auth-branding">
            <div class="auth-branding-panel">
                <span class="auth-branding-badge">Trusted rides • Easy bookings</span>
                <h1>WayGo</h1>
                <p>Welcome back — log in to manage your bookings and find the right ride for your next trip.</p>

                <div class="auth-branding-features">
                    <div class="auth-feature-item">
                        <span class="auth-feature-icon">✓</span>
                        Instant booking
                    </div>
                    <div class="auth-feature-item">
                        <span class="auth-feature-icon">✓</span>
                        Flexible rentals
                    </div>
                    <div class="auth-feature-item">
                        <span class="auth-feature-icon">✓</span>
                        Secure access
                    </div>
                    <div class="auth-feature-item">
                        <span class="auth-feature-icon">✓</span>
                        24/7 support
                    </div>
                </div>
            </div>
        </div>

        <div class="auth-form-panel">
            <div class="auth-form-box">
                <h2>Login</h2>
                <p class="auth-subtitle">Enter your details to continue.</p>

                <form action="login_process.php" method="POST" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="redirect" value="<?= isset($_GET['redirect']) ? e($_GET['redirect']) : '' ?>">

                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="<?= trim(field_error_class($field_errors, 'email')) ?>" value="<?= isset($old_input['email']) ? e($old_input['email']) : '' ?>">
                    <?php if (field_error($field_errors, 'email')) { ?>
                        <p class="field-inline-error">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
                            <?= e(field_error($field_errors, 'email')) ?>
                        </p>
                    <?php } ?>

                    <label for="password">Password</label>
                    <div class="password-field-wrapper">
                        <input type="password" id="password" name="password" class="<?= trim(field_error_class($field_errors, 'password')) ?>">
                        <button type="button" class="password-toggle-btn" data-target="password" aria-label="Show password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <?php if (field_error($field_errors, 'password')) { ?>
                        <p class="field-inline-error">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
                            <?= e(field_error($field_errors, 'password')) ?>
                        </p>
                    <?php } ?>

                    <button type="submit" class="auth-submit-btn">Login</button>
                </form>

                <p class="auth-switch-link">
                    Don't have an account?
                    <a href="register.php?redirect=<?= isset($_GET['redirect']) ? urlencode($_GET['redirect']) : '' ?>">Register here</a>
                </p>
            </div>
        </div>
    </div>

    <?php require '../includes/partials/toast.php'; ?>
    <script src="../js/auth.js"></script>
</body>
</html>