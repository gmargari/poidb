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
        return responseWithCodeMessage($response, 400, 'Not all required parameters are defined');
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
        return responseWithCodeMessage($response, 200, "OK");
    } else  {
        return responseWithCodeMessage($response, 500, 'Could not insert into db');
    }
};

//==============================================================================
// addTag ()
//==============================================================================
function addTag($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('oid', 'tag');
    if (!allParamsDefined($required, $params)) {
        return responseWithCodeMessage($response, 400, 'Not all required parameters are defined');
    }

    // Get parameters
    $oid = (string)$params['oid'];
    $tag = (string)$params['tag'];

    // Insert into database
    if (addTagToDB($oid, $tag)) {
        return responseWithCodeMessage($response, 200, "OK");
    } else  {
        return responseWithCodeMessage($response, 500, 'Could not insert into db');
    }
};

//==============================================================================
// addRating ()
//==============================================================================
function addRating($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('oid', 'rating', 'name');
    if (!allParamsDefined($required, $params)) {
        return responseWithCodeMessage($response, 400, 'Not all required parameters are defined');
    }

    // Get parameters
    $oid = (string)$params['oid'];
    $name = (string)$params['name'];
    $rating = (string)$params['rating'];

    // Insert into database
    if (addRatingToDB($oid, $name, $rating)) {
        return responseWithCodeMessage($response, 200, "OK");
    } else  {
        return responseWithCodeMessage($response, 500, 'Could not insert into db');
    }
};

//==============================================================================
// addComment ()
//==============================================================================
function addComment($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('oid', 'userId', 'text', 'time');
    if (!allParamsDefined($required, $params)) {
        return responseWithCodeMessage($response, 400, 'Not all required parameters are defined');
    }

    // Get parameters
    $oid = (string)$params['oid'];
    $userId = (string)$params['userId'];
    $text = (string)$params['text'];
    $time = (string)$params['time'];

    // Insert into database
    if (addCommentToDB($oid, $userId, $text, $time)) {
        return responseWithCodeMessage($response, 200, "OK");
    } else  {
        return responseWithCodeMessage($response, 500, 'Could not insert into db');
    }
};

//==============================================================================
// addPhoto ()
//==============================================================================
function addPhoto($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('oid', 'userId', 'src');
    if (!allParamsDefined($required, $params)) {
        return responseWithCodeMessage($response, 400, 'Not all required parameters are defined');
    }

    // Get parameters
    $oid = (string)$params['oid'];
    $userId = (string)$params['userId'];
    $src = (string)$params['src'];

    // Insert into database
    if (addPhotoToDB($oid, $userId, $src)) {
        return responseWithCodeMessage($response, 200, "OK");
    } else  {
        return responseWithCodeMessage($response, 500, 'Could not insert into db');
    }
};

//==============================================================================
// getPoisByLoc ()
//==============================================================================
function getPoisByLoc($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('longitude', 'latitude', 'max_distance');
    if (!allParamsDefined($required, $params)) {
        return responseWithCodeMessage($response, 400, 'Not all required parameters are defined');
    }

    // Get parameters
    $longitude = (double)$params['longitude'];
    $latitude = (double)$params['latitude'];
    $max_distance = (double)$params['max_distance'] * 1000;  // from km -> meters

    // Retrieve from database
    $result = array();
    if (getPoisFromDB($longitude, $latitude, $max_distance, $result)) {
        $result = json_encode($result);
        $response = $response->withHeader('Content-type', 'application/json');
        return responseWithCodeMessage($response, 200, $result);
    } else  {
        return responseWithCodeMessage($response, 500, 'Could not retrieve from db');
    }
};

//==============================================================================
// getComments ()
//==============================================================================
function getComments($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('oid');
    if (!allParamsDefined($required, $params)) {
        return responseWithCodeMessage($response, 400, 'Not all required parameters are defined');
    }

    // Get parameters
    $oid = (string)$params['oid'];

    // Retrieve from database
    $result = array();
    if (getCommentsFromDB($oid, $result)) {
        $result = json_encode($result);
        $response = $response->withHeader('Content-type', 'application/json');
        return responseWithCodeMessage($response, 200, $result);
    } else  {
        return responseWithCodeMessage($response, 500, 'Could not retrieve from db');
    }
};

//==============================================================================
// getPhotos ()
//==============================================================================
function getPhotos($request, $response, $args) {
    $params = $request->getParams();

    // Check all required parameters are defined
    $required = array('oid');
    if (!allParamsDefined($required, $params)) {
        return responseWithCodeMessage($response, 400, 'Not all required parameters are defined');
    }

    // Get parameters
    $oid = (string)$params['oid'];

    // Retrieve from database
    $result = array();
    if (getPhotosFromDB($oid, $result)) {
        $result = json_encode($result);
        $response = $response->withHeader('Content-type', 'application/json');
        return responseWithCodeMessage($response, 200, $result);
    } else  {
        return responseWithCodeMessage($response, 500, 'Could not retrieve from db');
    }
};
