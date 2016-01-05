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

    // Construct document to be inserted
    $doc = array(
        'location' => array(
            'coordinates' => array( $longitude, $latitude ),
            'type' => 'Point',
        ),
        'name' => $name,
    );

    // Insert document into database
    if (insertPoiIntoDB($doc)) {
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
	$response->getBody()->write("getPoisByLoc");
    return $response;
};
