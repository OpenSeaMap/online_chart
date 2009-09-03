<?PHP

/*
 Osm_Api_Service_Map
 Required: PHP 5 
 author Olaf Hannemann
 license GPL
 version 0.1.0
*/

	// get parameter values
	$_north = $_GET['n'];
	$_east = $_GET['e'];
	$_south = $_GET['s'];
	$_west = $_GET['w'];
	$_data = "bbox=" .$_west ."," .$_south ."," .$_east ."," .$_north;
	// Variables
	$_response = "error";					// Server response that will be send to client
	$_url = "api06.dev.openstreetmap.org";	// Url of the OSM server
	$_path = "/api/0.6/map?" .$_data;
	
	// Send to the OSM-Api
	function sendOSM($url, $path, $data) {
		$fp = @fsockopen($url, 80, $errno, $errstr);
		if (!$fp) {
			return "$errstr ($errno)\n";
		} else {
			fputs($fp, "GET " .$path ." HTTP/1.1\r\n");
			fputs($fp, "Host: " .$url ."\r\n");
			fputs($fp, "Accept:  text/xml; application/xml;q=0.9, application/xhtml+xml, image/x-xbitmap, */*;q=0.1\r\n");
			fputs($fp, "Connection: Keep-Alive\r\n\r\n");

			$response = "";
			$header = "not yet";
			while (!feof($fp)) {
				$line = fgets($fp, 1024);
				if( $line == "\r\n" && $header == "not yet" ) {
					$header = "passed";
				}
				if( $header == "passed" ) {
					$response .= $line;
				} 
			}
		}
		fclose($fp);

		return trim($response);
	}

	$_response = sendOSM($_url, $_path, $_data);
	
	echo $_response;

?> 