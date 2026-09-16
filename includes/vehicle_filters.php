<?php
/**
 * DOCU: Reads and validates vehicle browsing filters from the query string.
 * @returns {array} - Normalized filters and validation errors.
 *
 * Author: JC
 */
function get_vehicle_filters_from_request() {
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $transmission = isset($_GET['transmission']) ? trim($_GET['transmission']) : '';
    $min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? $_GET['min_price'] : 0;
    $max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? $_GET['max_price'] : 999999;
    $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'featured';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] >= 1 ? (int) $_GET['page'] : 1;

    $allowed_categories = array('sedan', 'suv', 'van', 'pickup_truck', 'motorcycle', 'coupe');
    $allowed_transmissions = array('automatic', 'manual');
    $allowed_sorts = array('featured', 'price_asc', 'price_desc', 'make_asc');

    $errors = array();

    if (!empty($category) && !in_array($category, $allowed_categories)) {
        $errors[] = 'Invalid category selected.';
    }

    if (!empty($transmission) && !in_array($transmission, $allowed_transmissions)) {
        $errors[] = 'Invalid transmission selected.';
    }

    if (!is_numeric($min_price) || !is_numeric($max_price) || $min_price < 0 || $max_price < $min_price) {
        $errors[] = 'Price range is invalid.';
    }

    if (!in_array($sort, $allowed_sorts)) {
    $sort = 'featured';
    }

    if (!isset($_SESSION['browse_shuffle_seed'])) {
        $_SESSION['browse_shuffle_seed'] = mt_rand(1, 999999);
    }

    return array(
        'category' => $category,
        'transmission' => $transmission,
        'min_price' => $min_price,
        'max_price' => $max_price,
        'sort' => $sort,
        'search' => $search,
        'page' => $page,
        'per_page' => 28,
        'errors' => $errors
    );
}

function get_vehicle_browse_state_key($filters) {
    $parts = array(
        'category' => $filters['category'],
        'transmission' => $filters['transmission'],
        'min_price' => (string) $filters['min_price'],
        'max_price' => (string) $filters['max_price'],
        'sort' => $filters['sort'],
        'search' => $filters['search'],
        'start_date' => isset($_GET['start_date']) ? $_GET['start_date'] : '',
        'end_date' => isset($_GET['end_date']) ? $_GET['end_date'] : ''
    );

    return md5(json_encode($parts));
}

function get_vehicle_browse_loaded_page($filters) {
    if (!isset($_SESSION['browse_loaded_pages']) || !is_array($_SESSION['browse_loaded_pages'])) {
        return 1;
    }

    $key = get_vehicle_browse_state_key($filters);
    return isset($_SESSION['browse_loaded_pages'][$key]) ? max(1, (int) $_SESSION['browse_loaded_pages'][$key]) : 1;
}

function set_vehicle_browse_loaded_page($filters, $page) {
    if (!isset($_SESSION['browse_loaded_pages']) || !is_array($_SESSION['browse_loaded_pages'])) {
        $_SESSION['browse_loaded_pages'] = array();
    }

    $key = get_vehicle_browse_state_key($filters);
    $_SESSION['browse_loaded_pages'][$key] = max(1, (int) $page);
}

/**
 * DOCU: Builds and runs the filtered vehicle query.
 * @param {mysqli} $connection - Active database connection.
 * @param {array} $filters - Normalized browse filters.
 * @param {?string} $start_date - Optional booking start date.
 * @param {?string} $end_date - Optional booking end date.
 * @returns {array} - Vehicle rows and pagination details.
 *
 * Author: JC
 */
function fetch_vehicles($connection, $filters, $start_date = null, $end_date = null) {
    $shuffle_seed = isset($_SESSION['browse_shuffle_seed']) ? $_SESSION['browse_shuffle_seed'] : 1;

    $sort_columns = array(
        'featured' => 'RAND(' . (int) $shuffle_seed . ')',
        'price_asc' => 'v.daily_rate ASC',
        'price_desc' => 'v.daily_rate DESC',
        'make_asc' => 'v.make ASC, v.model ASC'
    );
    $order_by = $sort_columns[$filters['sort']];

    $from = ' FROM vehicles v';
    $where = " WHERE v.status = 'available' AND v.daily_rate BETWEEN ? AND ?";
    $types = 'dd';
    $params = array($filters['min_price'], $filters['max_price']);

    if ($start_date !== null && $end_date !== null) {
        $from .= " LEFT JOIN bookings b
                    ON b.vehicle_id = v.id
                    AND b.status != 'cancelled'
                    AND b.start_date <= ?
                    AND b.end_date >= ?";
        $types = 'ss' . $types;
        $params = array_merge(array($end_date, $start_date), $params);
        $where .= ' AND b.id IS NULL';
    }

    if (!empty($filters['category'])) {
        $where .= ' AND v.category = ?';
        $types .= 's';
        $params[] = $filters['category'];
    }

    if (!empty($filters['transmission'])) {
        $where .= ' AND v.transmission = ?';
        $types .= 's';
        $params[] = $filters['transmission'];
    }

    if (!empty($filters['search'])) {
        $where .= ' AND (v.make LIKE ? OR v.model LIKE ?)';
        $types .= 'ss';
        $search_term = '%' . $filters['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
    }

    $count_sql = 'SELECT COUNT(*) AS total' . $from . $where;
    $count_stmt = mysqli_prepare($connection, $count_sql);
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $total = (int) mysqli_fetch_assoc($count_result)['total'];
    mysqli_stmt_close($count_stmt);

    $total_pages = max(1, (int) ceil($total / $filters['per_page']));
    $page = min($filters['page'], $total_pages);
    $offset = ($page - 1) * $filters['per_page'];

    $sql = 'SELECT v.*' . $from . $where . ' ORDER BY ' . $order_by . ' LIMIT ? OFFSET ?';
    $types .= 'ii';
    $params[] = $filters['per_page'];
    $params[] = $offset;

    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $vehicles = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    return array(
        'vehicles' => $vehicles,
        'total' => $total,
        'page' => $page,
        'total_pages' => $total_pages
    );
}