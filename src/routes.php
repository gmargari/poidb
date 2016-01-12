<?php
// TODO: remove in production mode
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//==============================================================================
// Mapping of routes to functions
//==============================================================================
$app->post('/poi', 'addPoi');
$app->get('/poi/getByLoc', 'getPoisByLoc');

require __DIR__ . '/../src/mongodb.php';
require __DIR__ . '/../src/util.php';

//==============================================================================
// addPoi ()
//==============================================================================
function addPoi($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('longitude', 'latitude', 'name');
    if (!allParamsDefined($required, $params)) {
        // TODO: better handling of errors
        $response->getBody()->write("Error: not all required parameters are defined");
        return $response;
    }

    // Get parameters
    $longitude = (double)$params['longitude'];
    $latitude = (double)$params['latitude'];
    $name = $params['name'];

    // Insert document into database
    if (insertPoiIntoDB($longitude, $latitude, $name)) {
        $response->getBody()->write("POI added");
    } else  {
        $response->getBody()->write("Error: could not insert document into db");
        return $response;
    }

    return $response;
};

//==============================================================================
// getPoisByLoc ()
//==============================================================================
function getPoisByLoc($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('longitude', 'latitude', 'max_distance');
    if (!allParamsDefined($required, $params)) {
        $response->getBody()->write("Error: not all required parameters are defined");
        return $response;
    }

    // Get parameters
    $longitude = (double)$params['longitude'];
    $latitude = (double)$params['latitude'];
    $max_distance = (double)$params['max_distance'];  // should be passed in meters

    // Get all pois that satisfy query and construct json to be returned
    $result_pois = array();
    if (getPoisFromDB($longitude, $latitude, $max_distance, $result_pois)) {
        $data = json_encode($result_pois, JSON_FORCE_OBJECT);
        $response = $response->withHeader('Content-type', 'application/json');
        $response->getBody()->write($data);
    } else  {
        $response->getBody()->write("Error: could not retrieve documents from db");
        return $response;
    }

    return $response;
};
