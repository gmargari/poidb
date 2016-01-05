<?php

// TODO: Move in separate file
Class Config {
    const mongo_host = "localhost:27017";
    const mongo_dbname = "poidb";
    const mongo_user = "poiuser";
    const mongo_pass = "poipass";
    const pois_col  = "pois";
};

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

function insertPoiIntoDB($object) {
    try {
        $db = connectMongo();
    } catch (MongoException $e) {
        return false;
    }

    $collection = Config::pois_col;
    try {
        $c = $db->$collection->insert($object);
        return true;
    } catch (MongoCursorException $e){
        return false;
    }
}
