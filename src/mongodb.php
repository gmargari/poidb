<?php

// TODO: Move in separate file
Class Config {
    const mongo_host = "localhost:27017";
    const mongo_dbname = "poidb";
    const mongo_user = "poiuser";
    const mongo_pass = "poipass";
    const pois_col  = "pois";
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
        return $db->$collection->update($query, $update, $options);
    } catch (MongoCursorException $e){
        return false; // TODO: differentiate between null (no results) and false/error
    }
}

//==============================================================================
// mongodbFind ()
//==============================================================================
function mongodbFind($collection, $query, $filter = array()) {  // TODO: private
    try {
        $db = connectMongo();
    } catch (MongoException $e) {
        return false;
    }

    try {
        return $db->$collection->find($query, $filter)->limit(0);
    } catch (MongoCursorException $e){
        return false; // TODO: differentiate between null (no results) and false/error
    }
} // TODO: query -> criteria

//==============================================================================
// ensureGeoSpatialIndexExistsInDB ()
//==============================================================================
function ensureGeoSpatialIndexForPoisInDB() {
    try {
        $db = connectMongo();
    } catch (MongoException $e) {
        return false;  // TODO: Better handling of error
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
        'tags' => array(),
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
    try {
        $cursor = mongodbFind($collection, $query);
    } catch (MongoException $e) {
        return false;  // TODO: Better handling of error
    }

    $result_pois = array();
    foreach ($cursor as $poi) {
        $oid = (string)$poi['_id'];
        $longitude = $poi['location']['coordinates'][0];
        $latitude = $poi['location']['coordinates'][1];
        $name = $poi['name'];
        $tags = $poi['tags'];
        $ratings = $poi['ratings'];
        $result_pois[$oid] = array(
            "oid" => $oid,
            "latitude" => $latitude,
            "longitude" => $longitude,
            "name" => $name,
            "tags" => $tags,
            "ratings" => $ratings,
        );
    }

    return true;
}
