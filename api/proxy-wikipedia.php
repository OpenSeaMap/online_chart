<?php
// PHP Proxy for map.openseamap.org to reach the wmflabs tool server

// Allowed hostname (api.local and api.travel are also possible here)
define ('HOSTNAME', 'https://tools.wmflabs.org/');

// Get the REST call path from the AJAX application
// Is it a POST or a GET?
$path = "wp-world/marks.php?LANG=".htmlspecialchars($_GET["LANG"]) .
    "&thumbs=".htmlspecialchars($_GET["thumbs"]) .
        "&bbox=".htmlspecialchars($_GET["bbox"]) ;

$url = HOSTNAME.$path;

// Open the Curl session
$session = curl_init($url);

// Don't return HTTP headers. Do return the contents of the call
curl_setopt($session, CURLOPT_HEADER, false);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

curl_setopt($session, CURLOPT_USERAGENT, "php-proxy for map.openseamap.org");

// Make the call
$xml = curl_exec($session);

// The web service returns XML. Set the Content-Type appropriately
header("Content-Type: text/xml");

echo $xml;
curl_close($session);

?>
