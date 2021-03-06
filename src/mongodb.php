<?php

// TODO: Move in separate file
Class Config {
    const mongo_host = 'localhost:27017';
    const mongo_dbname = 'poidb';
    const mongo_user = 'poiuser';
    const mongo_pass = 'poipass';
    const pois_col  = 'pois';
    const comments_col = 'comments';
    const photos_col = 'photos';
};

// TODO: Make class

//==============================================================================
// connectMongo ()
//==============================================================================
function connectMongo() {  // TODO: private
    try {
        $host = Config::mongo_host;
        $dbname = Config::mongo_dbname;
        $user = Config::mongo_user;
        $pass = Config::mongo_pass;
        // TODO: auth to mongo using user/pass
        //$url = "mongodb://$user:$pass@$host/$dbname";
        $url = "mongodb://$host/$dbname";
        $client = new MongoClient($url);
        $db = $client->$dbname;
        return $db;
    } catch (MongoException $e) {
        throw $e;
    }
}

//==============================================================================
// mongodbInsert ()
//==============================================================================
function mongodbInsert($collection, $doc) {  // TODO: private
    try {
        $db = connectMongo();
    } catch (MongoException $e) {
        handleException($e);
        return false;
    }

    try {
        $db->$collection->insert($doc);
        return true;
    } catch (MongoCursorException $e){
        handleException($e);
        return false;
    }
}

//==============================================================================
// mongodbUpdate ()
//==============================================================================
function mongodbUpdate($collection, $query, $update, $options = array()) {  // TODO: private
    try {
        $db = connectMongo();
    } catch (MongoException $e) {
        handleException($e);
        return false;
    }

    try {
        $db->$collection->update($query, $update, $options);
        return true;
    } catch (MongoCursorException $e){
        handleException($e);
        return false;
    }
}

//==============================================================================
// mongodbFind ()
//==============================================================================
function mongodbFind($collection, $query, &$cursor) {  // TODO: private
    try {
        $db = connectMongo();
    } catch (MongoException $e) {
        handleException($e);
        return false;
    }

    try {
        $cursor = $db->$collection->find($query)->limit(0);
        return true;
    } catch (MongoCursorException $e){
        handleException($e);
        return false;
    }
}

//==============================================================================
// mongodbFindOne ()
//==============================================================================
function mongodbFindOne($collection, $query, &$doc) {  // TODO: private
    try {
        $db = connectMongo();
    } catch (MongoException $e) {
        handleException($e);
        return false;
    }

    try {
        $doc = $db->$collection->findOne($query);
        return true;
    } catch (MongoCursorException $e){
        handleException($e);
        return false;
    }
}

//==============================================================================
// ensureGeoSpatialIndexExistsInDB ()
//==============================================================================
function ensureGeoSpatialIndexExistsInDB($collection, $field) {  // TODO: private
    try {
        $db = connectMongo();
    } catch (MongoException $e) {
        handleException($e);
        return false;
    }

    $db->$collection->ensureIndex(array($field => '2dsphere'));
    return true;
}

//==============================================================================
// ensureIndexExistsInDB ()
//==============================================================================
function ensureIndexExistsInDB($collection, $field) {  // TODO: private
    try {
        $db = connectMongo();
    } catch (MongoException $e) {
        handleException($e);
        return false;
    }

    $db->$collection->ensureIndex(array($field => 1));
    return true;
}

//==============================================================================
// addPoiToDB ()
//==============================================================================
function addPoiToDB($longitude, $latitude, $name, $url, $userId, $tags) {
    $collection = Config::pois_col;
    $doc = array(
        'location' => array(
            'coordinates' => array( $longitude, $latitude ),
            'type' => 'Point',
        ),
        'name' => $name,
        'tags' => $tags,
        'ratings' => array(),
        'userId' => $userId,
        'url' => $url,
    );
    return mongodbInsert($collection, $doc);
}

//==============================================================================
// addTagToDB ()
//==============================================================================
function addTagToDB($oid, $tag) {
    $collection = Config::pois_col;
    $query = array('_id' => new MongoId($oid));
    if (!mongodbFindOne($collection, $query, $doc)) {
        return false;
    } else if ($doc == NULL) {
        echo $oid . ' was not found in database';
        return false;
    }

    array_push($doc['tags'], $tag);
    return mongodbUpdate($collection, $query, $doc);
}

//==============================================================================
// addRatingToDB ()
//==============================================================================
function addRatingToDB($oid, $userId, $rating) {
    $collection = Config::pois_col;
    $query = array('_id' => new MongoId($oid));
    if (!mongodbFindOne($collection, $query, $doc)) {
        return false;
    } else if ($doc == NULL) {
        echo $oid . ' was not found in database';
        return false;
    }

    # If user has already rated this poi, replace old rating with new
    $found = false;
    foreach ($doc['ratings'] as &$doc_rating) {
        if ($doc_rating['userId'] == $userId) {
            $doc_rating['rating'] = $rating;
            $found = true;
            break;
        }
    }
    # Else insert a new rating
    if ($found == false) {
        array_push($doc['ratings'], array( "userId" => $userId, "rating" => $rating));
    }
    return mongodbUpdate($collection, $query, $doc);
}

//==============================================================================
// addCommentToDB ()
//==============================================================================
function addCommentToDB($oid, $userId, $text, $time) {
    $collection = Config::pois_col;
    $query = array('_id' => new MongoId($oid));
    if (!mongodbFindOne($collection, $query, $doc)) {
        return false;
    } else if ($doc == NULL) {
        echo $oid . ' was not found in database';
        return false;
    }

    $collection = Config::comments_col;
    $doc = array(
        'oid' => $oid,
        'userId' => $userId,
        'text' => $text,
        'time' => $time,
    );
    return mongodbInsert($collection, $doc);
}

//==============================================================================
// addPhotoToDB ()
//==============================================================================
function addPhotoToDB($oid, $userId, $src) {
    $collection = Config::pois_col;
    $query = array('_id' => new MongoId($oid));
    if (!mongodbFindOne($collection, $query, $doc)) {
        return false;
    } else if ($doc == NULL) {
        echo $oid . ' was not found in database';
        return false;
    }

    $collection = Config::photos_col;
    $doc = array(
        'oid' => $oid,
        'userId' => $userId,
        'src' => $src
    );
    return mongodbInsert($collection, $doc);
}

//==============================================================================
// getPoisFromDB ()
//==============================================================================
function getPoisFromDB($longitude, $latitude, $max_distance, &$result) {
    $collection = Config::pois_col;
    $query = array(
        'location' => array(
            '$near' => array(
                '$geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array($longitude, $latitude),
                ),
                '$maxDistance' => $max_distance,
            ),
        ),
    );
    ensureGeoSpatialIndexExistsInDB($collection, 'location');
    if (!mongodbFind($collection, $query, $cursor)) {
        return false;
    }

    $result = array();
    foreach ($cursor as $doc) {
        $oid = (string)$doc['_id'];
        $result[$oid] = array(  // TODO: $result[] =
            'oid' => $oid,
            'longitude' => (string)$doc['location']['coordinates'][0],
            'latitude' => (string)$doc['location']['coordinates'][1],
            'name' => $doc['name'],
            'tags' => $doc['tags'],
            'ratings' => $doc['ratings'],
            'userId' => $doc['userId'],
            'url' => $doc['url'],
        );
    }

    return true;
}

//==============================================================================
// getCommentsFromDB ()
//==============================================================================
function getCommentsFromDB($oid, &$result) {
    $collection = Config::comments_col;
    $query = array('oid' => $oid);
    ensureIndexExistsInDB($collection, 'oid');
    if (!mongodbFind($collection, $query, $cursor)) {
        echo $oid . ' was not found in database';
        return false;
    }

    $result = array();
    foreach ($cursor as $doc) {
        $result[] = array(
            'userId' => $doc['userId'],
            'text' => $doc['text'],
            'time' => $doc['time'],
        );
    }

    return true;
}

//==============================================================================
// getPhotosFromDB ()
//==============================================================================
function getPhotosFromDB($oid, &$result) {
    $collection = Config::photos_col;
    $query = array('oid' => $oid);
    ensureIndexExistsInDB($collection, 'oid');
    if (!mongodbFind($collection, $query, $cursor)) {
        echo $oid . ' was not found in database';
        return false;
    }

    $result = array();
    foreach ($cursor as $doc) {
        $result[] = array(
            'userId' => $doc['userId'],
            'src' => $doc['src'],
        );
    }

    return true;
}
