<?PHP

/******************************************************************************
 Osm_Api_Service_Map
 Required: PHP 5 
 author Olaf Hannemann
 license GPL
 version 0.1.1

 This file is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This file is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License (http://www.gnu.org/licenses/) for more details.
*******************************************************************************/


	// get parameter values
	$_north = $_GET['n'];
	$_east = $_GET['e'];
	$_south = $_GET['s'];
	$_west = $_GET['w'];
	$_data = "bbox=" .$_west ."," .$_south ."," .$_east ."," .$_north;
	// Variables
	$_response = "error";						// Server response that will be send to client
	//$_url = "api06.dev.openstreetmap.org";	// Url of the OSM dev server
	$_url = "www.openstreetmap.org";			// Url of the OSM server
	$_path = "/api/0.6/map?" .$_data;
	
	// Send to the OSM-Api
	function sendOSM($url, $path) {
		$fp = @fsockopen($url, 80, $errno, $errstr);
		if (!$fp) {
			return "$errstr ($errno)\n";
		} else {
			fputs($fp, "GET " .$path ." HTTP/1.1\r\n");
			fputs($fp, "Host: " .$url ."\r\n");
			fputs($fp, "User-Agent: OpenSeaMap-Editor (0.1.0)\r\n");
			fputs($fp, "Accept: text/html, *; q=.2, */*\r\n");
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

	$_response = sendOSM($_url, $_path);
	
	echo $_response;

?> 