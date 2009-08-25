<?PHP

/*
 Osm_Api_Service_Node
 Required: PHP 5 
 author Olaf Hannemann
 license GPL
 version 0.1.0
*/

	// get parameter values
	$_todo = $_GET['action'];
	$_changset_id = $_GET['changeset_id'];
	$_node_id = $_GET['node_id'];
	$_user_name = $_GET['name'];
	$_user_password = $_GET['password'];
	$_comment = $_GET['comment'];
	$_data = $_GET['data'];

	// Variables
	$_response = "error";					// Server response that will be send to client
	$_url = "api06.dev.openstreetmap.org";	// Url of the OSM server
	
	// create the needed osm-api url
	switch ($_todo) {
		case "create":
			$_path = "/api/0.6/node/create";
			$_method = "PUT";
			break;
		case "update":
			$buff = trim($_node_id);
			$_path = "/api/0.6/node/" .$buff;
			$_method = "PUT";
			break;
		case "get":
			$buff = trim($_node_id);
			$_path = "/api/0.6/node/" .$buff;
			$_method = "GET";
			break;
		case "delete":
			$buff = trim($_node_id);
			$_path = "/api/0.6/node/" .$buff;
			$_method = "DELETE";
			break;
		default:
			$_path = "error";
		}

	// Send to the OSM-Api
	function sendOSM($url, $path, $login, $data, $method) {
		$fp = @fsockopen($url, 80, $errno, $errstr);
		if (!$fp) {
			return "$errstr ($errno)\n";
		} else {
			fputs($fp, $method ." " .$path ." HTTP/1.1\r\n");
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

		return trim($line);
	}

	$_response = sendOSM($_url, $_path, base64_encode($_user_name .":" .$_user_password), $_data, $_method);
	
	echo $_response;

?> 