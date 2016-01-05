<?php

// Mapping of routes to functions
$app->post('/poi', 'addPoi');
$app->get('/poi/getByLoc', 'getPoisByLoc');

require __DIR__ . '/../src/mongodb.php';

// Function definitions
function addPoi($request, $response, $args) {
    $params = $request->getParams();

    // Construct document
    $longitude = (double)$params['longitude'];
    $latitude = (double)$params['latitude'];
    $name = $params['name'];
    $doc = array(
        'location' => array(
            'coordinates' => array( $longitude, $latitude ),
            'type' => 'Point',
        ),
        'name' => $name,
    );

    // Insert into database
    if (insertPoiIntoDB($doc)) {
        $response->getBody()->write("POI added");
    } else  {
        $response->getBody()->write("Error: could not insert document into db");
        return $response;
    }

    return $response;
};

function getPoisByLoc($request, $response, $args) {
	$response->getBody()->write("getPoisByLoc");
    return $response;
};
