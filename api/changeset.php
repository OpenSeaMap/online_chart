<?PHP

/******************************************************************************
 Osm_Api_Service_Changeset
 Required: PHP 5 
 author Olaf Hannemann
 license GPL V3
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
	$_todo = $_GET['action'];
	$_changset_id = $_GET['id'];
	$_user_name = $_GET['userName'];
	$_user_password = $_GET['userPassword'];
	$_comment = $_GET['comment'];

	// Variables
	$_response = "error";					// Server response that will be send to client
	$_url = "api06.dev.openstreetmap.org";	// Url of the OSM dev server
	//$_url = "api.openstreetmap.org";		// Url of the OSM server
	
	// create the needed osm-api url
	function createUrl($action, $id) {
		switch ($action) {
			case "create":
				$path = "/api/0.6/changeset/create";
				break;
			case "update":
				$path = "/api/0.6/changeset/" .$id;
				break;
			case "close":
				$path = "/api/0.6/changeset/" .$id ."/close";
				break;
			default:
				 $path = "error";
		}
		return trim($path);
	}

	// Create XML for a new changeset
	function createChangeSet($comment) {
		$xmlOSM = "<?xml version='1.0' encoding='UTF-8'?>\n";
		$xmlOSM .= "<osm> \n";
		$xmlOSM .= "<changeset>\n";
		$xmlOSM .= "<tag k=\"created_by\" v=\"OpenSeaMap-Editor-0.1.2\"/>\n";
		$xmlOSM .= "<tag k=\"comment\" v=\"" .$comment ."\"/>";
		$xmlOSM .= "\n</changeset>\n</osm>";

		return $xmlOSM;
	}

	// Send to the OSM-Api
	function sendOSM($url, $path, $login, $data) {
		$status = "";
		$response = "";
		$fp = @fsockopen($url, 80, $errno, $errstr);
		if (!$fp) {
			return "$errstr ($errno)\n";
		} else {
			fputs($fp, "PUT " .$path ." HTTP/1.1\r\n");
			fputs($fp, "Host: " .$url ."\r\n");
			fputs($fp, "User-Agent: OpenSeaMap-Editor/0.1.2\r\n");
			fputs($fp, "Authorization: Basic " .$login ."\r\n");
			fputs($fp, "Content-type:  text/xml; charset=utf-8\r\n");
			fputs($fp, "Content-length: ". strlen($data) ."\r\n");
			fputs($fp, "Connection: Keep-Alive\r\n\r\n");
			fputs($fp, $data ."\r\n");

			$header = "not yet";
			while (!feof($fp)) {
				$line = fgets($fp, 1024);
				if( $line == "\r\n" && $header == "not yet" ) {
					$header = "passed";
				}
				if( $header == "passed" ) {
					$response .= $line;
				} /*else {
					$arg = split(":", $line);
					if ($arg[0] == "status") {
						$status .= trim($arg[1]);
					}
				}*/
			}
		}
		fclose($fp);
		/*if ($status != "200") {
			$response = "Error:" .$status;
		}*/
		return trim($response);
	}

	$_response = sendOSM($_url, createUrl($_todo, $_changset_id), base64_encode($_user_name .":" .$_user_password), createChangeSet($_comment));

	echo $_response;
?> 