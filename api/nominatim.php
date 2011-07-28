<?php
/***************************
 * Nominatim Handler
 * as firefox returns a xmlhttps request with status=0 we need a local handler 
 * Gerit Wissing, 17.04.2011
 ***************************/
header("Content-Type: text/xml; charset=utf-8");
$name = urlencode( $_GET["q"]  );
$baseUrl = 'http://nominatim.openstreetmap.org/search?format=xml&q=';
$data = file_get_contents( "{$baseUrl}{$name}" );
echo $data;
?>
