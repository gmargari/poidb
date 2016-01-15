#!/usr/bin/python
import json
import sys
import os

# To import a GeoJSON file:
# ./transform_json_for_mongoimport.py file.geojson | mongoimport --db poidb --collection pois
#
# To drop existing database:
# mongo
#   use poidb
#   db.runCommand( { dropDatabase: 1 } )

attraction_tag_mapping = {
    "Ancient Ruins" : "Monument",
    "Historic Sites" : "Monument",
    "Monuments & Statues" : "Monument",
    "Architectural Buildings" : "Landmark",
    "Observation Decks & Towers" : "Landmark",
    "Points of Interest & Landmarks" : "Landmark",
    "Churches & Cathedrals" : "Religious Site",
    "Religious Sites" : "Religious Site",
    "Science Museums" : "Museum",
    "Art Museums" : "Museum",
    "History Museums" : "Museum",
    "Specialty Museums" : "Museum",
    "Convention Centers" : "Other",
    "Dams" : "Other",
    "Exhibitions" : "Other",
    "Flea & Street Markets" : "Other",
    "Shopping Malls" : "Other",
    "Sports Complexes" : "Other",
    "Theaters" : "Other",
}

if (len(sys.argv) != 2):
    print "Syntax: " + sys.argv[0] + " file.geojson"
    sys.exit(1)

if (not os.path.isfile(sys.argv[1])):
    print "Error: file '" + sys.argv[1] + "' does not exist."
    sys.exit(1)

with open(sys.argv[1]) as data_file:
    geojson_objects = json.load(data_file)
    features = geojson_objects['features']

    for feature in features:
        lon = float(feature['geometry']['coordinates'][0])
        lat = float(feature['geometry']['coordinates'][1])
        doc = {}
        doc["location"]  = { "coordinates" : [ lon, lat ], "type" : "Point" }
        doc['name'] = feature['properties']['name']
        doc['tags'] = feature['properties']['tags']
        doc['url'] = feature['properties']['url']
        doc['userId'] = "imported_from_geojson"
        doc['ratings'] = [ ]

        # Map tags to a predifed set of 5 tags used by app:
        #   Landmark, Monument, Museum, Religious Sites, Restaurant, Other

        if feature['properties']['type'] == "attraction":
            # Use a set to remove duplicates
            doc['tags'] = list(set([ attraction_tag_mapping[tag] for tag in doc['tags'] ]))
        elif feature['properties']['type'] == "restaurant":
            doc['tags'] = [ "Restaurant" ]

        print json.dumps(doc)
