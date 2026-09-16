<?php
require '../includes/auth.php';
require '../config/database.php';
require '../includes/helpers.php';
require '../includes/vehicle_filters.php';

$filters = get_vehicle_filters_from_request();

$vehicles = array();
$pagination = array('total' => 0, 'page' => 1, 'total_pages' => 1);

if (empty($filters['errors'])) {
    $result = fetch_vehicles($connection, $filters);
    $vehicles = $result['vehicles'];
    $pagination = array(
        'total' => $result['total'],
        'page' => $result['page'],
        'total_pages' => $result['total_pages']
    );
    set_vehicle_browse_loaded_page($filters, 1);
}

$query_params = $_GET;
unset($query_params['page']);
$query_string = http_build_query($query_params);

require '../includes/partials/vehicle_results_cards.php';