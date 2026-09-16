<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * DOCU: Returns the role used for display while respecting preview mode.
 * @returns {?string} - Effective display role.
 *
 * Author: JC
 */
function get_effective_role() {
    if (isset($_SESSION['preview_role'])) {
        return $_SESSION['preview_role'];
    }
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf_token() {
    $submitted_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (empty($submitted_token) && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $submitted_token = $_SERVER['HTTP_X_CSRF_TOKEN'];
    }

    if (!is_string($submitted_token) || !hash_equals(csrf_token(), $submitted_token)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}

function app_base_path() {
    $document_root = realpath($_SERVER['DOCUMENT_ROOT']);
    $project_root = realpath(dirname(__DIR__));

    if ($document_root === false || $project_root === false) {
        return '';
    }

    $base_path = str_replace('\\', '/', substr($project_root, strlen($document_root)));
    return rtrim($base_path, '/');
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . app_base_path() . '/auth/login.php');
        exit();
    }
}

function require_role($allowed_roles) {
    require_login();
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header('Location: ' . app_base_path() . '/auth/login.php');
        exit();
    }
}