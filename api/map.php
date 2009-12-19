<?PHP

/******************************************************************************
 Osm_Api_Service_Map
 Required: PHP 5 
 author Olaf Hannemann
 version 0.1.2

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
	//$_url = "api06.dev.openstreetmap.org";	// Url of the OSM dev server
	$_url = "api.openstreetmap.org";			// Url of the OSM server
	$_path = "/api/0.6/map?" .$_data;
	
	// Send to the OSM-Api
	$fp = @fsockopen($_url, 80, $errno, $errstr);
	if (!$fp) {
		return "$errstr ($errno)\n";
	} else {
		fputs($fp, "GET " .$_path ." HTTP/1.1\r\n");
		fputs($fp, "Host: " .$_url ."\r\n");
		fputs($fp, "User-Agent: OpenSeaMap-Editor (0.1.1)\r\n");
		fputs($fp, "Accept: text/html, *; q=.2, */*\r\n");
		fputs($fp, "Keep-Alive:	timeout=15, max=99\r\n");
		fputs($fp, "Connection: Keep-Alive\r\n\r\n");
		$response = "";
		$header = "not yet";
		while (!feof($fp)) {
			$line = fgets($fp);
			if( $line == "\r\n" && $header == "not yet" ) {
				$header = "passed";
			}
			if( $header == "passed" ) {
				$buff = trim($line);
				if (substr($buff, 0, 4) == "<way") {
					echo "</osm>" ."\r\n";
					break;
				}
				if ($buff != "") {
					echo $buff ."\n";
				}
			}
		}
	}
	fclose($fp);

?> 