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
    <title>WayGo - Register</title>
    <link rel="stylesheet" href="../css/style.css?v=6">
</head>
<body>
    <div class="auth-page">
        <div class="auth-branding">
            <div class="auth-branding-panel">
                <span class="auth-branding-badge">Ready when you are</span>
                <h1>WayGo</h1>
                <p>Find the right ride for every trip — from everyday vehicles to sport cars and motorcycles.</p>

                <div class="auth-branding-features">
                    <div class="auth-feature-item">
                        <span class="auth-feature-icon">✓</span>
                        Wide vehicle range
                    </div>
                    <div class="auth-feature-item">
                        <span class="auth-feature-icon">✓</span>
                        Quick approval
                    </div>
                    <div class="auth-feature-item">
                        <span class="auth-feature-icon">✓</span>
                        Flexible pickup
                    </div>
                    <div class="auth-feature-item">
                        <span class="auth-feature-icon">✓</span>
                        Better prices
                    </div>
                </div>
            </div>
        </div>

        <div class="auth-form-panel">
            <div class="auth-form-box">
                <h2>Create an Account</h2>
                <p class="auth-subtitle">Sign up to start booking vehicles.</p>

                <form action="register_process.php" method="POST" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="redirect" value="<?= isset($_GET['redirect']) ? e($_GET['redirect']) : '' ?>">

                    <div class="auth-name-row">
                        <div>
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="<?= trim(field_error_class($field_errors, 'first_name')) ?>" value="<?= isset($old_input['first_name']) ? e($old_input['first_name']) : '' ?>">
                            <?php if (field_error($field_errors, 'first_name')) { ?>
                                <p class="field-inline-error">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
                                    <?= e(field_error($field_errors, 'first_name')) ?>
                                </p>
                            <?php } ?>
                        </div>
                        <div>
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="<?= trim(field_error_class($field_errors, 'last_name')) ?>" value="<?= isset($old_input['last_name']) ? e($old_input['last_name']) : '' ?>">
                            <?php if (field_error($field_errors, 'last_name')) { ?>
                                <p class="field-inline-error">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
                                    <?= e(field_error($field_errors, 'last_name')) ?>
                                </p>
                            <?php } ?>
                        </div>
                    </div>

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

                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-field-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" class="<?= trim(field_error_class($field_errors, 'confirm_password')) ?>">
                        <button type="button" class="password-toggle-btn" data-target="confirm_password" aria-label="Show password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <?php if (field_error($field_errors, 'confirm_password')) { ?>
                        <p class="field-inline-error">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
                            <?= e(field_error($field_errors, 'confirm_password')) ?>
                        </p>
                    <?php } ?>

                    <button type="submit" class="auth-submit-btn">Create Account</button>
                </form>

                <p class="auth-switch-link">
                    Already have an account?
                    <a href="login.php?redirect=<?= isset($_GET['redirect']) ? urlencode($_GET['redirect']) : '' ?>">Login here</a>
                </p>
            </div>
        </div>
    </div>

    <?php require '../includes/partials/toast.php'; ?>
    <script src="../js/auth.js"></script>
</body>
</html>