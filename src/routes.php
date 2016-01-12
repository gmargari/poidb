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
$app->post('/tag', 'addTag');
$app->post('/rating', 'addRating');

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
        $response->getBody()->write('Error: not all required parameters are defined');
        return $response;
    }

    // Get parameters
    $longitude = (double)$params['longitude'];
    $latitude = (double)$params['latitude'];
    $name = $params['name'];

    // Insert document into database
    if (insertPoiIntoDB($longitude, $latitude, $name)) {
        $response->getBody()->write('POI added');
    } else  {
        $response->getBody()->write('Error: could not insert document into db');
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
        $response->getBody()->write('Error: not all required parameters are defined');
        return $response;
    }

    // Get parameters
    $longitude = (double)$params['longitude'];
    $latitude = (double)$params['latitude'];
    $max_distance = (double)$params['max_distance'];  // should be passed in meters

    // Get all pois that satisfy query and construct json to be returned
    $result_pois = array();
    if (getPoisFromDB($longitude, $latitude, $max_distance, $result_pois)) {
        $data = json_encode($result_pois);
        $response = $response->withHeader('Content-type', 'application/json');
        $response->getBody()->write($data);
    } else  {
        $response->getBody()->write('Error: could not retrieve documents from db');
        return $response;
    }

    return $response;
};

//==============================================================================
// addTag ()
//==============================================================================
function addTag($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('oid', 'tag');
    if (!allParamsDefined($required, $params)) {
        $response->getBody()->write('Error: not all required parameters are defined');
        return $response;
    }

    // Get parameters
    $oid = (string)$params['oid'];
    $tag = (string)$params['tag'];

    // Insert tag into database
    if (addTagToDB($oid, $tag)) {
        $response->getBody()->write('Tag added');
    } else  {
        $response->getBody()->write('Error: could not insert tag to db');
        return $response;
    }

    return $response;
};

//==============================================================================
// addRating ()
//==============================================================================
function addRating($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('oid', 'rating', 'name');
    if (!allParamsDefined($required, $params)) {
        $response->getBody()->write('Error: not all required parameters are defined');
        return $response;
    }

    // Get parameters
    $oid = (string)$params['oid'];
    $name = (string)$params['name'];
    $rating = (string)$params['rating'];

    // Insert rating into database
    if (addRatingToDB($oid, $name, $rating)) {
        $response->getBody()->write('Rating added');
    } else  {
        $response->getBody()->write('Error: could not insert rating to db');
        return $response;
    }

    return $response;
};
