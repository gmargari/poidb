<?php

// TODO: Move in separate file
Class Config {
    const mongo_host = 'localhost:27017';
    const mongo_dbname = 'poidb';
    const mongo_user = 'poiuser';
    const mongo_pass = 'poipass';
    const pois_col  = 'pois';
};

//==============================================================================
// handleException ()
//==============================================================================
function handleException($e) {
    echo "\n";
    echo 'Exception in ' . $e->getFile() . ':' . $e->getLine() . ' : "' . $e->getMessage() . '"';
}

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
        echo $e;
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
function mongodbFind($collection, $query, $filter, &$cursor) {  // TODO: private
    try {
        $db = connectMongo();
    } catch (MongoException $e) {
        return false;
    }

    try {
        $cursor = $db->$collection->find($query, $filter)->limit(0);
        return true;
    } catch (MongoCursorException $e){
        handleException($e);
        return false;
    }
} // TODO: query -> criteria

//==============================================================================
// mongodbFindOne ()
//==============================================================================
function mongodbFindOne($collection, $query, $filter, &$doc) {  // TODO: private
    try {
        $db = connectMongo();
    } catch (MongoException $e) {
        return false;
    }

    try {
        $doc = $db->$collection->findOne($query, $filter);
        return true;
    } catch (MongoCursorException $e){
        handleException($e);
        return false;
    }
}

//==============================================================================
// ensureGeoSpatialIndexExistsInDB ()
//==============================================================================
function ensureGeoSpatialIndexForPoisInDB() {
    try {
        $db = connectMongo();
    } catch (MongoException $e) {
        handleException($e);
        return false;
    }

    $collection = Config::pois_col;
    $db->$collection->ensureIndex(array('location' => '2dsphere'));
    return true;
}

//==============================================================================
// insertPoiIntoDB ()
//==============================================================================
function insertPoiIntoDB($longitude, $latitude, $name) {

    // Construct document to be inserted
    $doc = array(
        'location' => array(
            'coordinates' => array( $longitude, $latitude ),
            'type' => 'Point',
        ),
        'name' => $name,
        'tag' => array(),
        'ratings' => array(),
    );
    $collection = Config::pois_col;
    return mongodbInsert($collection, $doc);
}

//==============================================================================
// getPoisFromDB ()
//==============================================================================
function getPoisFromDB($longitude, $latitude, $max_distance, &$result_pois) {
    // Construct query
    // (https://docs.mongodb.org/v3.0/tutorial/query-a-2dsphere-index/#proximity-to-a-geojson-point)
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
    ensureGeoSpatialIndexForPoisInDB();
    $filter = array();
    if (!mongodbFind($collection, $query, $filter, $cursor)) {
        return false;
    }

    $result_pois = array();
    foreach ($cursor as $poi) {
        $oid = (string)$poi['_id'];
        $longitude = $poi['location']['coordinates'][0];
        $latitude = $poi['location']['coordinates'][1];
        $name = $poi['name'];
        $tags = $poi['tag'];
        $ratings = $poi['ratings'];
        $result_pois[$oid] = array(
            'oid' => $oid,
            'latitude' => (string)$latitude,
            'longitude' => (string)$longitude,
            'name' => $name,
            'tag' => $tags,
            'ratings' => $ratings,
        );
    }

    return true;
}

//==============================================================================
// addTagToDB ()
//==============================================================================
function addTagToDB($oid, $tag) {
    $collection = Config::pois_col;
    $query = array('_id' => new MongoId($oid));
    $filter = array();

    if (!mongodbFindOne($collection, $query, $filter, $doc)) {
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
    $filter = array();

    if (!mongodbFindOne($collection, $query, $filter, $doc)) {
        return false;
    } else if ($doc == NULL) {
        echo $oid . ' was not found in datase';
        return false;
    }

    $new_rating = array( "name" => $name, "rating" => $rating);
    array_push($doc['ratings'], $new_rating);
    return mongodbUpdate($collection, $query, $doc);
}
