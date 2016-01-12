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
$app->post('/comment', 'addComment');
$app->get('/comment', 'getComments');

require __DIR__ . '/../src/mongodb.php';
require __DIR__ . '/../src/util.php';

//==============================================================================
// addPoi ()
//==============================================================================
function addPoi($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('longitude', 'latitude', 'name', 'userId', 'tag', 'url');
    if (!allParamsDefined($required, $params)) {
        // TODO: better handling of errors
        $response->getBody()->write('Error: not all required parameters are defined');
        // TODO: return proper response code
        return $response;
    }

    // Get parameters
    $longitude = (double)$params['longitude'];
    $latitude = (double)$params['latitude'];
    $name = $params['name'];
    $url = $params['url'];
    $userId = $params['userId'];
    $tags = $params['tag'];

    // Insert document into database
    if (insertPoiIntoDB($longitude, $latitude, $name, $url, $userId, $tags)) {
        $response->getBody()->write('POI added');
    } else  {
        $response->getBody()->write('Error: could not insert POI into db');
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
    $max_distance = (double)$params['max_distance'] * 1000;  // from km -> meters

    // Get all pois that satisfy query and construct json to be returned
    $result = array();
    if (getPoisFromDB($longitude, $latitude, $max_distance, $result)) {
        $result_json = json_encode($result);
        $response = $response->withHeader('Content-type', 'application/json');
        $response->getBody()->write($result_json);
    } else  {
        $response->getBody()->write('Error: could not retrieve POIs from db');
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
        $response->getBody()->write('Error: could not insert tag into db');
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
        $response->getBody()->write('Error: could not insert rating into db');
    }
    return $response;
};

//==============================================================================
// addComment ()
//==============================================================================
function addComment($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('oid', 'userId', 'text', 'time');
    if (!allParamsDefined($required, $params)) {
        $response->getBody()->write('Error: not all required parameters are defined');
        return $response;
    }

    // Get parameters
    $oid = (string)$params['oid'];
    $userId = (string)$params['userId'];
    $text = (string)$params['text'];
    $time = (string)$params['time'];

    // Insert comment into database
    if (addCommentToDB($oid, $userId, $text, $time)) {
        $response->getBody()->write('Comment added');
    } else  {
        $response->getBody()->write('Error: could not insert comment into db');
    }
    return $response;
};

//==============================================================================
// getComments ()
//==============================================================================
function getComments($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('oid');
    if (!allParamsDefined($required, $params)) {
        $response->getBody()->write('Error: not all required parameters are defined');
        return $response;
    }

    // Get parameters
    $oid = (string)$params['oid'];

    // Get comments from database
    $result = array();
    if (getCommentsFromDB($oid, $result)) {
        $result_json = json_encode($result);
        $response = $response->withHeader('Content-type', 'application/json');
        $response->getBody()->write($result_json);
    } else  {
        $response->getBody()->write('Error: could not retrieve comments from db');
    }
    return $response;
};
