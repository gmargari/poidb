<?php

// TODO: Move in separate file
Class Config {
    const mongo_host = "localhost:27017";
    const mongo_dbname = "poidb";
    const mongo_user = "poiuser";
    const mongo_pass = "poipass";
    const pois_col  = "pois";
};

//==============================================================================
// connectMongo ()
//==============================================================================
function connectMongo() {
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
// getPoisFromDB ()
//==============================================================================
function getPoisFromDB($query) {
    try {
        $db = connectMongo();
    } catch (MongoException $e) {
        return false;
    }

    $collection = Config::pois_col;
    try {
        // Ensure geospatial index exists
        $db->$collection->ensureIndex(array('location' => '2dsphere'));
        return $db->$collection->find($query)->limit(0);
    } catch (MongoCursorException $e){
        return false; // TODO: differentiate between null (no results) and false/error
    }
}
