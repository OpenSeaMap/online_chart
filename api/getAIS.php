<?php
// -----------------------------------------------------------------------------
// AIS Layer - OpenLayer class to display AIS traffic
//
// Written in 2011 by Dominik Fässler
//
// To the extent possible under law, the author(s) have dedicated all copyright
// and related and neighboring rights to this software to the public domain
// worldwide. This software is distributed without any warranty.
//
// You should have received a copy of the CC0 Public Domain Dedication along
// with this software. If not, see
// <http://creativecommons.org/publicdomain/zero/1.0/>.
// -----------------------------------------------------------------------------


// -----------------------------------------------------------------------------
// Description
// -----------------------------------------------------------------------------
// This is a proxy script which fetches AIS data from 'www.marinetraffic.com'.
//
// Unfortunately, the OpenLayers BBOX strategy does not submit the zoom
// level, so we always fetch objects with highest zoom level. Due to the fact
// that we don't know the filtering algorithm on the remote service, it is not
// such a bad strategy.
//
// If the requested range is to large, the remote service will return an empty
// result set. Zoom level 8 upwards will work.
// -----------------------------------------------------------------------------


// -----------------------------------------------------------------------------
// Information from 'marinetraffic.com'
// -----------------------------------------------------------------------------
// As a start for your practice, please feel free to use the following URL
// to retrieve real-time AIS positions in XML:
//
//   http://www.marinetraffic.com/ais/getxml_i.aspx?
//       sw_x=24.0&sw_y=34.0&ne_x=30.0&ne_y=39.5&zoom=14
//
// You must specify SW and NE bounds of the area you wish to download. Zoom
// level is used to 'cluster' (i.e. grouping together) data in smaller zoom
// levels, in order to avoid having too many overlapping markers drawn.
// In the XML data returned you will notice the following fields:
//
// LAT, LON
// N= ship's name
// T= ship's type
// H= Heading in degrees
// S= Speed in knots multiplied by 10
// F= Ship's Flag
// M= MMSI number
// L= ship's length
// E= time elapsed since received, in minutes
// -----------------------------------------------------------------------------


// -----------------------------------------------------------------------------
// Write XML header
// -----------------------------------------------------------------------------

header('Content-Type: text/xml; charset=utf-8');


// -----------------------------------------------------------------------------
// Parse parameters
// -----------------------------------------------------------------------------

$bbox = explode(',', $_REQUEST['bbox']);

if (count($bbox) !== 4) {
    print '<POSITIONS></POSITIONS>';
    return;
}

$swX = round($bbox[0] * 10000) / 10000;
$swY = round($bbox[1] * 10000) / 10000;
$neX = round($bbox[2] * 10000) / 10000;
$neY = round($bbox[3] * 10000) / 10000;


// -----------------------------------------------------------------------------
// Fetch data
// -----------------------------------------------------------------------------

$host = 'www.marinetraffic.com';
$url  = '/ais/getxml_i.aspx';
$url .= '?sw_x=' . $swX;
$url .= '&sw_y=' . $swY;
$url .= '&ne_x=' . $neX;
$url .= '&ne_y=' . $neY;
$url .= '&zoom=18';

$fp = fsockopen($host, 80, $errno, $errstr, 10);

if (!$fp) {
    print '<POSITIONS></POSITIONS>';
    return;
}

$contents = '';

$out  = 'GET ' . $url . ' HTTP/1.1' . "\r\n";
$out .= 'Host: ' . $host . "\r\n";
$out .= 'Connection: Close' . "\r\n\r\n";
fwrite($fp, $out);
while (!feof($fp)) {
    $contents .= fgets($fp, 128);
}
fclose($fp);

list($header, $contents) = preg_split( '/([\r\n][\r\n])\\1/', $contents, 2);


// -----------------------------------------------------------------------------
// Output data
// -----------------------------------------------------------------------------

print strip_tags($contents, '<POSITIONS><V_POS>');
