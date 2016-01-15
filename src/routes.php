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
    $required_params = array('longitude', 'latitude', 'name', 'userId', 'tags', 'url');
    if (!allParamsDefined($required_params, $params)) {
        return responseWithCodeMessage($response, 400, 'Parameter missing');
    }

    $longitude = (double)$params['longitude'];
    $latitude = (double)$params['latitude'];
    $name = (string)$params['name'];
    $userId = (string)$params['userId'];
    $tags = $params['tags'];
    foreach ($tags as &$tag) {
        $tag = (string)$tag;
    }
    $url = (string)$params['url'];

    if (addPoiToDB($longitude, $latitude, $name, $url, $userId, $tags)) {
        return responseWithCodeMessage($response, 200, "OK");
    } else  {
        return responseWithCodeMessage($response, 500, 'Could not insert into db');
    }
}

//==============================================================================
// addTag ()
//==============================================================================
function addTag($request, $response, $args) {
    $params = $request->getParams();
    $required_params = array('oid', 'tag');
    if (!allParamsDefined($required_params, $params)) {
        return responseWithCodeMessage($response, 400, 'Parameter missing');
    }

    $oid = (string)$params['oid'];
    $tag = (string)$params['tag'];

    if (addTagToDB($oid, $tag)) {
        return responseWithCodeMessage($response, 200, "OK");
    } else  {
        return responseWithCodeMessage($response, 500, 'Could not insert into db');
    }
}

//==============================================================================
// addRating ()
//==============================================================================
function addRating($request, $response, $args) {
    $params = $request->getParams();
    $required_params = array('oid', 'rating', 'userId');
    if (!allParamsDefined($required_params, $params)) {
        return responseWithCodeMessage($response, 400, 'Parameter missing');
    }

    $oid = (string)$params['oid'];
    $userId = (string)$params['userId'];
    $rating = (string)$params['rating'];

    if (addRatingToDB($oid, $userId, $rating)) {
        return responseWithCodeMessage($response, 200, "OK");
    } else  {
        return responseWithCodeMessage($response, 500, 'Could not insert into db');
    }
}

//==============================================================================
// addComment ()
//==============================================================================
function addComment($request, $response, $args) {
    $params = $request->getParams();
    $required_params = array('oid', 'userId', 'text', 'time');
    if (!allParamsDefined($required_params, $params)) {
        return responseWithCodeMessage($response, 400, 'Parameter missing');
    }

    $oid = (string)$params['oid'];
    $userId = (string)$params['userId'];
    $text = (string)$params['text'];
    $time = (string)$params['time'];

    if (addCommentToDB($oid, $userId, $text, $time)) {
        return responseWithCodeMessage($response, 200, "OK");
    } else  {
        return responseWithCodeMessage($response, 500, 'Could not insert into db');
    }
}

//==============================================================================
// addPhoto ()
//==============================================================================
function addPhoto($request, $response, $args) {
    $params = $request->getParams();
    $required_params = array('oid', 'userId', 'src');
    if (!allParamsDefined($required_params, $params)) {
        return responseWithCodeMessage($response, 400, 'Parameter missing');
    }

    $oid = (string)$params['oid'];
    $userId = (string)$params['userId'];
    $src = (string)$params['src'];

    if (addPhotoToDB($oid, $userId, $src)) {
        return responseWithCodeMessage($response, 200, "OK");
    } else  {
        return responseWithCodeMessage($response, 500, 'Could not insert into db');
    }
}

//==============================================================================
// getPoisByLoc ()
//==============================================================================
function getPoisByLoc($request, $response, $args) {
    $params = $request->getParams();
    $required_params = array('longitude', 'latitude', 'max_distance');
    if (!allParamsDefined($required_params, $params)) {
        return responseWithCodeMessage($response, 400, 'Parameter missing');
    }

    $longitude = (double)$params['longitude'];
    $latitude = (double)$params['latitude'];
    $max_distance = (double)$params['max_distance'] * 1000;  // from km -> meters

    $result = array();
    if (getPoisFromDB($longitude, $latitude, $max_distance, $result)) {
        $result = json_encode($result);
        $response = $response->withHeader('Content-type', 'application/json');
        return responseWithCodeMessage($response, 200, $result);
    } else  {
        return responseWithCodeMessage($response, 500, 'Database error');
    }
}

//==============================================================================
// getComments ()
//==============================================================================
function getComments($request, $response, $args) {
    $params = $request->getParams();
    $required_params = array('oid');
    if (!allParamsDefined($required_params, $params)) {
        return responseWithCodeMessage($response, 400, 'Parameter missing');
    }

    $oid = (string)$params['oid'];

    $result = array();
    if (getCommentsFromDB($oid, $result)) {
        $result = json_encode($result);
        $response = $response->withHeader('Content-type', 'application/json');
        return responseWithCodeMessage($response, 200, $result);
    } else  {
        return responseWithCodeMessage($response, 500, 'Database error');
    }
}

//==============================================================================
// getPhotos ()
//==============================================================================
function getPhotos($request, $response, $args) {
    $params = $request->getParams();
    $required_params = array('oid');
    if (!allParamsDefined($required_params, $params)) {
        return responseWithCodeMessage($response, 400, 'Parameter missing');
    }

    $oid = (string)$params['oid'];

    $result = array();
    if (getPhotosFromDB($oid, $result)) {
        $result = json_encode($result);
        $response = $response->withHeader('Content-type', 'application/json');
        return responseWithCodeMessage($response, 200, $result);
    } else  {
        return responseWithCodeMessage($response, 500, 'Database error');
    }
}
