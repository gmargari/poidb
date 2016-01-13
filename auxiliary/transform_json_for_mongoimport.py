#!/usr/bin/python
import json
import sys
import os

# To import sample POIs:
# ./transform_json_for_mongoimport.py sample_POIs.json | mongoimport --db poidb --collection pois
#
# To drop existing database:
# mongo
#   use poidb
#   db.runCommand( { dropDatabase: 1 } )

if (len(sys.argv) != 2):
    print "Syntax: " + sys.argv[0] + " <sample_json_file>"
    sys.exit(1)

if (not os.path.isfile(sys.argv[1])):
    print "Error: file '" + sys.argv[1] + "' does not exist."
    sys.exit(1)

with open(sys.argv[1]) as data_file:
    json_objects = json.load(data_file)

    for oid in json_objects:
        doc = json_objects[oid]
        objid = doc["oid"]
        lat = float(doc["latitude"])
        lon = float(doc["longitude"])
        doc["location"]  = { "coordinates" : [ lon, lat ], "type" : "Point" }
        doc["_id"] = { '$oid' : objid }
        del doc["latitude"]
        del doc["longitude"]
        del doc["oid"]

        print json.dumps(doc)
