# Dependencies of our map

The following external sites/services are used to display the various layers.

## OSM base layer

### OpenStreetMap server
- http://a.tile.openstreetmap.org/11/1088/657.png
- http://b.tile.openstreetmap.org/11/1088/657.png
- http://c.tile.openstreetmap.org/11/1088/657.png

### Our tile server (Bravo)
- http://osm1.wtnet.de/tiles/base/11/1088/657.png


All should return a png tile with the size 256x256 px.

## Seamarks
t1.openseamap.org

### Examples
- http://t1.openseamap.org/seamark/14/8384/5405.png

Should return a png tile with the size 256x256 px.

## Harbours

Fetches data from http://dev.openseamap.org

(The php scripts queries a local database with seems to run on dev.openseamap.org)

### Examples
- http://dev.openseamap.org/website/map/api/getHarbours.php?b=43.16098&t=43.46375&l=16.23863&r=17.39219&ucid=0&maxSize=5&zoom=11

Should return a ```text/html``` file with some content like:
```
putHarbourMarker(1226, 16.396666666667, 43.163333333333, 'Sv._Klement - Marina PalmiÅ¾ana', 'http://www.skipperguide.de/wiki/Sv._Klement#Marina_Palmi.C5.BEana', '5');
```

## Tidal scale

Fetches data from external servers:

### Scales in Germany

- http://gauges.openseamap.smurf.noris.de/getTides.php?b=53.58321&t=53.70186&l=11.17168&r=11.72615

Should return a ```text/javascript``` file that should call a function ```putTidalScaleMarker(...)```.

### Scales in Switzerland
- http://osmch.chaosdwarfs.de/web/getTidalTest.php?b=44.66288&t=49.03975&l=1.80299&r=19.54591

Should return a ```text/javascript``` file that should call a function ```putTidalScaleMarker(...)```.

### Information in the popup

#### Weather
- http://weather.openportguide.de/cgi-bin/weather.pl/weather.png?var=meteogram&nx=614&ny=750

Should return a ```image/png```.

#### Pegel Online
- http://www.pegelonline.wsv.de/gast/stammdaten?pegelnr=9340020

Should return a ```text/html``` website.

## Sport layer
- http://t1.openseamap.org/sport/8/132/81.png

Should return a png tile with the size 256x256 px.

## Aerial photo (Bing)
Bing should deliver tiles. (How to test?)

## Elevation Profile

### Contours
- http://korona.geog.uni-heidelberg.de/tiles/asterc/?x=139&y=84&z=8

Should return an image.

### Hillshade
- http://korona.geog.uni-heidelberg.de/tiles/asterh/?x=139&y=84&z=8

Should return an image.

## Marine Profile
- http://osm.franken.de:8080/geoserver/wms?LAYERS=gebco%3Adeepshade_2014&PROJECTION=EPSG%3A900913&FORMAT=image%2Fpng&TRANSPARENT=true&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&SRS=EPSG%3A900913&BBOX=2504688.5425,7514065.6275,3130860.678125,8140237.763125&WIDTH=256&HEIGHT=256

Should return a ```image/png```.

## Wikipedia links

Queries data via api/proxy-wikipedia.php

- https://tools.wmflabs.org/wp-world/marks.php

Should return a kml file.

## Marine Traffic

- http://mob0.marinetraffic.com/ais/de/getxml_i.aspx?sw_x=24.0&sw_y=34.0&ne_x=30.0&ne_y=39.5&zoom=14

Should return a xml file.

## Water depth

- http://osm.franken.de/cgi-bin/mapserv.fcgi?PROJECTION=EPSG%3A900913&TYPE=png&TRANSPARENT=TRUE&LAYERS=trackpoints_cor1_test_dbs_10,trackpoints_cor1_test_10,test_zoom_10_cor_1_points_10,test_zoom_9_cor_1_points_10,test_zoom_8_cor_1_points_10,test_zoom_7_cor_1_points_10,test_zoom_6_cor_1_points_10,test_zoom_5_cor_1_points_10,test_zoom_4_cor_1_points_10,test_zoom_3_cor_1_points_10,test_zoom_2_cor_1_points_10&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&FORMAT=image%2Fpng&SRS=EPSG%3A900913&BBOX=1095801.2373438,7044436.5257813,1252344.27125,7200979.5596875&WIDTH=1024&HEIGHT=1024

Should return a ```image/png```.

## Depth Contours

- http://osm.franken.de/cgi-bin/mapserv.fcgi?LAYERS=contour,contour2&NUMZOOMLEVELS=22&TYPE=png&TRANSPARENT=TRUE&SERVICE=WMS&VERSION=1.1.1&REQUEST=GetMap&STYLES=&FORMAT=image%2Fpng&SRS=EPSG%3A900913&BBOX=1252344.27125,7044436.5257813,1408887.3051562,7200979.5596875&WIDTH=1024&HEIGHT=1024

Should return a ```image/png```.

## Download chart

- http://map.openseamap.org/gml/map_download.xml

Should return a ```text/xml```.

## Search

Fetched via api/nominatim.php

- http://nominatim.openstreetmap.org/search?format=xml&q=schwerin

Should return a ```text/xml```.
