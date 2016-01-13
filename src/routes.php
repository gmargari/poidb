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
$app->post('/photo', 'addPhoto');
$app->get('/photo', 'getPhotos');

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
        // TODO: better handling of errors, return proper response code
        $response->getBody()->write('Error: not all required parameters are defined');
        return $response;
    }

    // Get parameters
    $longitude = (double)$params['longitude'];
    $latitude = (double)$params['latitude'];
    $name = $params['name'];
    $url = $params['url'];
    $userId = $params['userId'];
    $tags = $params['tag'];

    // Insert into database
    if (addPoiToDB($longitude, $latitude, $name, $url, $userId, $tags)) {
        $response->getBody()->write('Ok');
    } else  {
        $response->getBody()->write('Error: could not insert into db');
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

    // Insert into database
    if (addTagToDB($oid, $tag)) {
        $response->getBody()->write('Ok');
    } else  {
        $response->getBody()->write('Error: could not insert into db');
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

    // Insert into database
    if (addRatingToDB($oid, $name, $rating)) {
        $response->getBody()->write('Ok');
    } else  {
        $response->getBody()->write('Error: could not insert into db');
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

    // Insert into database
    if (addCommentToDB($oid, $userId, $text, $time)) {
        $response->getBody()->write('Ok');
    } else  {
        $response->getBody()->write('Error: could not insert into db');
    }
    return $response;
};

//==============================================================================
// addPhoto ()
//==============================================================================
function addPhoto($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('oid', 'userId', 'src');
    if (!allParamsDefined($required, $params)) {
        $response->getBody()->write('Error: not all required parameters are defined');
        return $response;
    }

    // Get parameters
    $oid = (string)$params['oid'];
    $userId = (string)$params['userId'];
    $src = (string)$params['src'];

    // Insert into database
    if (addPhotoToDB($oid, $userId, $src)) {
        $response->getBody()->write('Ok');
    } else  {
        $response->getBody()->write('Error: could not insert into db');
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

    // Retrieve from database
    $result = array();
    if (getPoisFromDB($longitude, $latitude, $max_distance, $result)) {
        $result_json = json_encode($result);
        $response = $response->withHeader('Content-type', 'application/json');
        $response->getBody()->write($result_json);
    } else  {
        $response->getBody()->write('Error: could not retrieve from db');
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

    // Retrieve from database
    $result = array();
    if (getCommentsFromDB($oid, $result)) {
        $result_json = json_encode($result);
        $response = $response->withHeader('Content-type', 'application/json');
        $response->getBody()->write($result_json);
    } else  {
        $response->getBody()->write('Error: could not retrieve from db');
    }
    return $response;
};

//==============================================================================
// getPhotos ()
//==============================================================================
function getPhotos($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('oid');
    if (!allParamsDefined($required, $params)) {
        $response->getBody()->write('Error: not all required parameters are defined');
        return $response;
    }

    // Get parameters
    $oid = (string)$params['oid'];

    // Retrieve from database
    $result = array();
    if (getPhotosFromDB($oid, $result)) {
        $result_json = json_encode($result);
        $response = $response->withHeader('Content-type', 'application/json');
        $response->getBody()->write($result_json);
    } else  {
        $response->getBody()->write('Error: could not retrieve from db');
    }
    return $response;
};
