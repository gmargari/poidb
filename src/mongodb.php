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
// insertPoiIntoDB ()
//==============================================================================
function insertPoiIntoDB($doc) {
    try {
        $db = connectMongo();
    } catch (MongoException $e) {
        return false;
    }

    $collection = Config::pois_col;
    try {
        $db->$collection->insert($doc);
        return true;
    } catch (MongoCursorException $e){
        return false;
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
// getPoisFromDB ()
//==============================================================================
function getPoisFromDB($query) {
    $collection = Config::pois_col;
    ensureGeoSpatialIndexForPoisInDB();
    return mongodbFind($collection, $query);
}
