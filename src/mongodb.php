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
} // TODO: query -> criteria

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

    // Construct document to be inserted
    $doc = array(
        'location' => array(
            'coordinates' => array( $longitude, $latitude ),
            'type' => 'Point',
        ),
        'name' => $name,
        'tag' => $tags,
        'ratings' => array(),
        'userId' => $userId,
        'url' => $url,
    );
    $collection = Config::pois_col;
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
        echo $oid . ' was not found in datase';
        return false;
    }

    array_push($doc['tag'], $tag);
    return mongodbUpdate($collection, $query, $doc);
//    return mongodbUpdate($collection, $query, array('$push' => array('tags', $tag)); // exception: Invalid modifier specified: $push
}

//==============================================================================
// addRatingToDB ()
//==============================================================================
function addRatingToDB($oid, $name, $rating) {
    $collection = Config::pois_col;
    $query = array('_id' => new MongoId($oid));

    if (!mongodbFindOne($collection, $query, $doc)) {
        return false;
    } else if ($doc == NULL) {
        echo $oid . ' was not found in datase';
        return false;
    }

    array_push($doc['ratings'], array( "name" => $name, "rating" => $rating));
    return mongodbUpdate($collection, $query, $doc);
}

//==============================================================================
// addCommentToDB ()
//==============================================================================
function addCommentToDB($oid, $userId, $text, $time) {
    $collection = Config::pois_col;
    $query = array('_id' => new MongoId($oid));

    // Check if oid exists in pois collection
    if (!mongodbFindOne($collection, $query, $doc)) {
        return false;
    } else if ($doc == NULL) {
        echo $oid . ' was not found in datase';
        return false;
    }

    // Construct document to be inserted
    $doc = array(
        'oid' => $oid,
        'userId' => $userId,
        'text' => $text,
        'time' => $time,
    );
    $collection = Config::comments_col;
    return mongodbInsert($collection, $doc);
}

//==============================================================================
// addPhotoToDB ()
//==============================================================================
function addPhotoToDB($oid, $userId, $src) {
    $collection = Config::pois_col;
    $query = array('_id' => new MongoId($oid));

    // Check if oid exists in pois collection
    if (!mongodbFindOne($collection, $query, $doc)) {
        return false;
    } else if ($doc == NULL) {
        echo $oid . ' was not found in datase';
        return false;
    }

    // Construct document to be inserted
    $doc = array(
        'oid' => $oid,
        'userId' => $userId,
        'src' => $src
    );
    $collection = Config::photos_col;
    return mongodbInsert($collection, $doc);
}

//==============================================================================
// getPoisFromDB ()
//==============================================================================
function getPoisFromDB($longitude, $latitude, $max_distance, &$result) {
    // https://docs.mongodb.org/v3.0/tutorial/query-a-2dsphere-index/#proximity-to-a-geojson-point
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

    $collection = Config::pois_col;
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
            'tag' => $doc['tag'],
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
    $query = array('oid' => $oid);

    $collection = Config::comments_col;
    ensureIndexExistsInDB($collection, 'oid');
    if (!mongodbFind($collection, $query, $cursor)) {
        echo $oid . ' was not found in datase';
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
    $query = array('oid' => $oid);

    $collection = Config::photos_col;
    ensureIndexExistsInDB($collection, 'oid');
    if (!mongodbFind($collection, $query, $cursor)) {
        echo $oid . ' was not found in datase';
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
