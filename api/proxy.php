<?php
/**
 * This proxy class is a copy/paste from https://github.com/GeniusGeeek/cors-bypass-proxy
 * It's very useful in dev environment to bypass ssl certificate verification and
 * to bypass CORS check from the browser.
 * 
 * Only difference with the github code of GeniuGeek is that i have added the curl option:
 * CURLOPT_SSL_VERIFYHOST => false.
 * 
 * Avoid using this when possible. Currently only use to requests harbours.
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST,GET,OPTIONS");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Origin,Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With,Access-Control-Allow-Credentials');

//retrieve request headers
$headers = array();
foreach (getallheaders() as $header_name => $header_value) {
  if (strpos($header_name, 'Content-Type') === 0  ||  strpos($header_name, 'Authorization') === 0 || strpos($header_name, 'X-Requested-With') === 0) {
    $header_name = strtolower($header_name);
    $headers[] =  $header_name . ":" . $header_value;
  }
}

$is_json_string_request = json_decode(file_get_contents("php://input"));

if (json_last_error() === JSON_ERROR_NONE) {
  //request is a json string request
  $is_json_request = true;

  //request validation
  if (!isset($is_json_string_request->method) || empty($is_json_string_request->method)) {
    echo json_encode(array("message" => "PROXY ACESS DENIED!, Request method not specified"));
    exit();
  }
  if (!isset($is_json_string_request->cors) || empty($is_json_string_request->cors)) {
    echo json_encode(array("message" => "PROXY ACESS DENIED!, cors endpoint not specified"));
    exit();
  }

  if ($_SERVER['REQUEST_METHOD'] != $is_json_string_request->method) {

    echo json_encode(array("message" => "PROXY ACESS DENIED!, Request type and method must be the same"));
    exit();
  }

  $url = $is_json_string_request->cors;
  $method = $is_json_string_request->method;
} else {
  //request is a raw request
  $is_raw_request = true;

  //request validation
  if (!isset($_REQUEST['method']) || empty($_REQUEST['method'])) {
    echo json_encode(array("message" => "PROXY ACESS DENIED!, Request method not specified"));
    exit();
  }
  if (
    !isset($_REQUEST['cors']) || empty($_REQUEST['cors'])
  ) {
    echo json_encode(array("message" => "PROXY ACESS DENIED!, cors endpoint not specified"));
    exit();
  }

  if (
    $_SERVER['REQUEST_METHOD'] != $_REQUEST['method']
  ) {

    echo json_encode(array("message" => "PROXY ACESS DENIED!, Request type and method must be the same"));
    exit();
  }
  $url = $_REQUEST['cors'];
  $method = $_REQUEST['method'];
}


switch ($method) {
  case "POST":

    /*@param
     unset cors and method POST values used by only this proxy and not needed by called API endpoint
    */
    if (isset($is_json_request) && $is_json_request == true) {
      $post_keys_values = (array) $is_json_string_request;
      unset($post_keys_values['cors']);
      unset($post_keys_values['method']);
    }
    if (isset($is_raw_request) && $is_raw_request == true) {
      $post_keys_values = $_POST;
      unset($post_keys_values['cors']);
      unset($post_keys_values['method']);

      //retrieve POST parameters
      $keys = "";
      $values = "";
      foreach ($post_keys_values as $key => $value) {
        $keys .= $key . '%%';
        $values .= $value . '%%';
      };
      $post_keys = explode('%%', $keys);
      $post_values = explode('%%', $values);
      $post_parameters = array_combine($post_keys, $post_values);
    }


    


    //prepare POST parameters
    if (isset($is_json_request) && $is_json_request == true) {
      $post_parameters = json_encode($post_keys_values);
    } else {
      $post_parameters = http_build_query($post_parameters);
    }



    //initiate CURL request
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL            => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST  => $method,
      CURLOPT_SSL_VERIFYPEER => false, // remove this on production 
      CURLOPT_POSTFIELDS     => $post_parameters,
      CURLOPT_HTTPHEADER     => $headers



    ));
    break;

  case "GET":

    /*@param
     unset cors and method GET values used by only this proxy and not needed by called API endpoint
    */
    if (isset($is_json_request) && $is_json_request == true) {
      $get_keys_values = (array) $is_json_string_request;
      unset($get_keys_values['cors']);
      unset($get_keys_values['method']);
    }
    if (isset($is_raw_request) && $is_raw_request == true) {
      $get_keys_values = $_GET;
      unset($get_keys_values['cors']);
      unset($get_keys_values['method']);

      //retrieve GET parameters
      $keys = "";
      $values = "";
      foreach ($get_keys_values as $key => $value) {
        $keys .= $key . '%%';
        $values .= $value . '%%';
      };
      $get_keys = explode('%%', $keys);
      $get_values = explode('%%', $values);
      $get_parameters = array_combine($get_keys, $get_values);
    }

    

    //prepare GET parameters
    if (isset($is_json_request) && $is_json_request == true) {
      $get_params = json_encode($get_keys_values);
    } else {
      $get_params = http_build_query($get_parameters);
    }

    //initiate CURL request
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL            => $url . "?" . $get_params,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => false, // remove this on production 
      CURLOPT_HTTPHEADER     => $headers,
      CURLOPT_SSL_VERIFYHOST => false,
    ));
    break;

  default:
    //you may copy code from POST block and put here if you need more request types and you know what you're doing
    echo json_encode(array("message" => "Proxy only allows POST and GET request"));


    exit();
}


$response = curl_exec($curl);

$err      = curl_error($curl);

curl_close($curl);

if ($err) {
  echo json_encode($err);
} else {
  json_decode($response);
  if (json_last_error() === JSON_ERROR_NONE) {
    header('Content-Type: application/json');
  }
  echo $response;
}