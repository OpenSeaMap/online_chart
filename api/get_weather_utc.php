<?PHP

/******************************************************************************
 Weather
 Required: PHP 5 
 author Olaf Hannemann
 license GPL
 version 0.1.0

 This file is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This file is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License (http://www.gnu.org/licenses/) for more details.
*******************************************************************************/


class Weather {

	// Variables
	$_response = "";						// Server response that will be send to client
	$_url = "http://www.openportguide.org";	// Url of the OSM dev server

	function getWeatherUtc($time) {
		// Send to the OSM-Api
		$fp = @fsockopen($_url, 80, $errno, $errstr);
		if (!$fp) {
			return "$errstr ($errno)\n";
		} else {
			fputs($fp, "GET /api/0.6/node/" .$_node_id ." HTTP/1.1\r\n");
			fputs($fp, "Host: " .$_url ."\r\n");
			fputs($fp, "Authorization: Basic " .$login ."\r\n");
			fputs($fp, "Content-type:  text/xml; charset=utf-8\r\n");
			fputs($fp, "Connection: Keep-Alive\r\n\r\n");

			$header = "not yet";
			while (!feof($fp)) {
				$line = fgets($fp, 1024);
				if( $line == "\r\n" && $header == "not yet" ) {
					$header = "passed";
				}
				if( $header == "passed" ) {
					$_response .= $line;
				}
			}
		}
		fclose($fp);
		//$_response = $response;

		echo trim($_response);
	}
}

?> 