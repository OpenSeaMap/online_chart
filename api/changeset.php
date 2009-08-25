<?PHP

/*
 Osm_Api_Service_Changeset
 Required: PHP 5 
 author Olaf Hannemann
 license GPL
 version 0.1.0
*/

	// get parameter values
	$_todo = $_GET['action'];
	$_changset_id = $_GET['id'];
	$_user_name = $_GET['userName'];
	$_user_password = $_GET['userPassword'];
	$_comment = $_GET['comment'];

	// Variables
	$_response = "error";					// Server response that will be send to client
	$_url = "api06.dev.openstreetmap.org";	// Url of the OSM server
	
	// create the needed osm-api url
	function createUrl($action, $id) {
		switch ($action) {
			case "create":
				$path = "api/0.6/changeset/create";
				break;
			case "update":
				$path = "api/0.6/changeset/" .$id;
				break;
			case "close":
				$path = "api/0.6/changeset/" .$id ."/close";
				break;
			default:
				 $path = "error";
		}

		return $url;
	}

	// Create XML for a new changeset
	function createChangeSet($comment) {
		$xmlOSM = "<?xml version='1.0' encoding='UTF-8'?>\n";
		$xmlOSM .= "<osm> \n";
		$xmlOSM .= "<changeset>\n";
		$xmlOSM .= "<tag k=\"created_by\" v=\"openseamap_editor_v0.89\"/>\n";
		$xmlOSM .= "<tag k=\"comment\" v=\"" .$comment ."\"/>";
		$xmlOSM .= "\n</changeset>\n</osm>";

		return $xmlOSM;
	}

	// Send to the OSM-Api
	function sendOSM($url, $path, $login, $data) {
		$fp = @fsockopen($url, 80, $errno, $errstr);
		if (!$fp) {
			return "$errstr ($errno)\n";
		} else {
			fputs($fp, "PUT " .$path ." HTTP/1.1\r\n");
			fputs($fp, "Host: " .$url ."\r\n");
			fputs($fp, "Authorization: Basic " .$login ."\r\n");
			fputs($fp, "Content-type:  text/xml; charset=utf-8\r\n");
			fputs($fp, "Content-length: ". strlen($data) ."\r\n");
			fputs($fp, "Connection: Keep-Alive\r\n\r\n");
			fputs($fp, $data ."\r\n");

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

	$_response = sendOSM($_url, "/api/0.6/changeset/create", base64_encode($_user_name .":" .$_user_password), createChangeSet($_comment));
	
	echo $_response;

?> 