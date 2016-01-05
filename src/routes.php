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

    // Construct query
    // (https://docs.mongodb.org/v3.0/tutorial/query-a-2dsphere-index/#proximity-to-a-geojson-point)
    $query = Array(
        'location' => Array(
            '$near' => Array(
                '$geometry' => Array(
                    'type' => 'Point',
                    'coordinates' => Array($longitude, $latitude),
                ),
                '$maxDistance' => $max_distance,
            ),
        ),
    );

    // Get all pois that satisfy query and construct json to be returned
    if (($cursor = getPoisFromDB($query))) {
        $result_pois = array();
        foreach ($cursor as $poi) {
            $oid = (String)$poi['_id'];
            $longitude = $poi['location']['coordinates'][0];
            $latitude = $poi['location']['coordinates'][1];
            $name = $poi['name'];
            $result_pois[$oid] = array(
                "oid" => $oid,
                "latitude" => $latitude,
                "longitude" => $longitude,
                "name" => $name,
            );
        }
        $data = json_encode($result_pois, JSON_FORCE_OBJECT);
        $response = $response->withHeader('Content-type', 'application/json');
        $response->getBody()->write($data);
    } else  {
        $response->getBody()->write("Error: could not insert document into db");
        return $response;
    }

    return $response;
};
