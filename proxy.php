<?php
if( $_SERVER['REMOTE_ADDR'] !== $_SERVER['SERVER_ADDR'] ) {
	http_response_code(400);
	exit;
}

// include "pa.php"; //debugging

define("BASE_URL", "ger-integrator.tactica.is/ws/getSingleproduct");
define("HEADER_PREFIX", "X-PROXY-");


function getRequestHeders(){
	$headers = array();
	if( $request = apache_request_headers() ){
		foreach( $request as $key => $value ){
			if( strpos($key, HEADER_PREFIX) !== FALSE ){
				$key = str_replace(HEADER_PREFIX, '', $key);
				$headers[] = "$key: $value";
			}
		}
	}	
	return $headers;
}

function getRequestUrl(){
	$url = BASE_URL;

	if( !empty($_POST) ){
		$fields_string = '';
		foreach( $_POST as $key => $value ) {
			$fields_string .= $key.'='.$value.'&'; 
		}
		$fields_string = rtrim($fields_string, '&');
		$url .= '?';
		$url .= $fields_string;
	}
	return $url;
}

function httpCrosRequest(){

	if( !function_exists("curl_init") ) die("cURL extension is not installed");

	$requestURL = getRequestUrl();
	$headers = getRequestHeders();
	$timeout = 30;	
	
	$curl = curl_init();

	curl_setopt ($curl, CURLOPT_URL, $requestURL);
	curl_setopt ($curl, CURLOPT_FAILONERROR, true);
	curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt ($curl, CURLOPT_MAXREDIRS, 20);
	curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.5) Gecko/2008120122 Firefox/3.0.5");
	curl_setopt ($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, $timeout);

	$response = curl_exec($curl);
	$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  $curl_errno = curl_errno($curl);

	curl_close($curl);

  http_response_code($http_status);

  if( $curl_errno ){
  	echo $curl_errno;
  } else {
  	echo $response;
  }

  die();
}

httpCrosRequest();

?>