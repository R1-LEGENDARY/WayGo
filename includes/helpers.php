<?php
/**
 * DOCU: Escapes a value for safe HTML output.
 * @param {mixed} $value - Value to escape.
 * @returns {string} - HTML-safe string.
 *
 * Author: JC
 */
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * DOCU: Restricts redirects to internal application paths.
 * @param {string} $target - Requested redirect target.
 * @param {string} $default - Fallback path when the target is unsafe.
 * @returns {string} - Safe redirect path.
 *
 * Author: JC
 */
function safe_internal_redirect($target, $default) {
    if (empty($target)) {
        return $default;
    }
    if (preg_match('#^https?://#i', $target) || strpos($target, '//') === 0) {
        return $default;
    }
    return $target;
}

/**
 * DOCU: Checks whether an email domain has mail servers configured.
 * @param {string} $email - Email address to validate.
 * @returns {bool} - Whether the domain has an MX or A record.
 *
 * Author: JC
 */
function email_domain_can_receive_mail($email) {
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return false;
    }
    $domain = $parts[1];
    return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
}

/**
 * DOCU: Gets the validation message for a form field.
 * @param {array} $field_errors - Field errors keyed by field name.
 * @param {string} $field_name - Field name to inspect.
 * @returns {string} - Matching error message or an empty string.
 *
 * Author: JC
 */
function field_error($field_errors, $field_name) {
    return isset($field_errors[$field_name]) ? $field_errors[$field_name] : '';
}

/**
 * DOCU: Builds the error class for a form field.
 * @param {array} $field_errors - Field errors keyed by field name.
 * @param {string} $field_name - Field name to inspect.
 * @returns {string} - CSS class suffix or an empty string.
 *
 * Author: JC
 */
function field_error_class($field_errors, $field_name) {
    return isset($field_errors[$field_name]) ? ' has-error' : '';
}

/**
 * DOCU: Builds a human-readable, collision-safe filename for an uploaded image.
 * @param {string} $category - Vehicle category.
 * @param {string} $make - Vehicle make.
 * @param {string} $model - Vehicle model.
 * @param {string} $original_filename - Original uploaded filename.
 * @returns {string} - Generated upload filename.
 *
 * Author: JC
 */
function generate_upload_filename($category, $make, $model, $original_filename) {
    $extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

    $slug_source = $make . ' ' . $model;
    $slug = strtolower(trim($slug_source));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');

    $category_slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $category));
    $timestamp = date('Ymd-His');
    $random_suffix = substr(bin2hex(random_bytes(3)), 0, 6);

    return $category_slug . '_' . $slug . '_' . $timestamp . '_' . $random_suffix . '.' . $extension;
}

/**
 * DOCU: Returns the home breadcrumb for the current viewer.
 * @returns {array} - Breadcrumb label and URL.
 *
 * Author: JC
 */
function home_breadcrumb() {
    if (function_exists('is_logged_in') && is_logged_in() && function_exists('get_effective_role') && get_effective_role() === 'admin') {
        return array('label' => 'Dashboard', 'url' => '../dashboard/index.php');
    }
    return array('label' => 'Browse', 'url' => '../vehicles/browse.php');
}