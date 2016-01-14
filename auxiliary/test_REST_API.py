#!/usr/bin/python
import sys
import requests
import json

url = 'http://localhost/poidb/public/'

pois = [
  { "longitude": "22.941178", "latitude": "40.636600", "name": "name1", "url": "http://url1.com", "userId": "userid1", "tags": [ "tag1" ] },
  { "longitude": "22.959927", "latitude": "40.624414", "name": "name2", "url": "http://url2.com", "userId": "userid2", "tags": [ "tag1", "tag2" ] },
  { "longitude": "22.951743", "latitude": "40.632241", "name": "name3", "url": "http://url3.com", "userId": "userid3", "tags": [ "tag2", "tag3" ] },
  { "longitude": "22.954503", "latitude": "40.641796", "name": "name4", "url": "http://url4.com", "userId": "userid4", "tags": [ "tag3", "tag4", "tag5" ] },
]

ratings = [
  { "userId": "useridx", "rating":"1" },
  { "userId": "useridx", "rating":"2" },
  { "userId": "useridx", "rating":"3" },
]

comments = [
  { "userId": "useridy", "text": "commentext1", "time": "today" },
  { "userId": "useridy", "text": "commentext2", "time": "yesterday" },
  { "userId": "useridy", "text": "commentext3", "time": "tommorow" },
]

photos = [
  { "userId": "useridz", "src": "base64image1" },
  { "userId": "useridz", "src": "base64image2" },
  { "userId": "useridz", "src": "base64image3" },
]

def checkResponse(res):
    if (res.status_code != 200):
        print res.status_code, ' : ', res.text
        sys.exit(1)

print 'Add POIs'
for poi in pois:
    res = requests.post(url + '/poi', poi)
    checkResponse(res)

print 'Get POIs'
res = requests.get(url + 'poi/getByLoc?latitude=' + pois[0]['latitude'] + '&longitude=' + pois[0]['longitude'] + '&max_distance=10')
checkResponse(res)

# Get oid of the first poi in results
res_pois = json.loads(res.text)
poi_id = res_pois.keys()[0]

print 'Add ratings'
for rating in ratings:
    rating['oid'] = poi_id
    res = requests.post(url + '/rating', rating)
    checkResponse(res)

print 'Add comments'
for comment in comments:
    comment['oid'] = poi_id
    res = requests.post(url + '/comment', comment)
    checkResponse(res)

print 'Get comments'
res = requests.get(url + '/comment?oid=' + poi_id)
checkResponse(res)

print 'Add photos'
for photo in photos:
    photo['oid'] = poi_id
    res = requests.post(url + '/photo', photo)
    checkResponse(res)

print 'Get photos'
res = requests.get(url + '/photo?oid=' + poi_id)
checkResponse(res)

print "Everything ok!"
