<?php
require_once(dirname(__FILE__) . "/../../../config/linker.php");
include("RestRequest.php");
ob_start("ob_gzhandler");
$requestUrl = $_SERVER["REQUEST_URI"];
$pathInfo = $_SERVER["PATH_INFO"];
$queryString = $_SERVER["QUERY_STRING"];
$getData = $_GET;
$postData = $_POST;
$request = NULL;
try {
    $request = RestRequest::factory(
        $requestUrl,
        $pathInfo,
        $queryString,
        $getData,
        $postData
    );
    $response = $request->process();
    $formattedResponse = $request->formatResponse();
    $headerPairList = $request->responseHeader();
    foreach ( $headerPairList as $headerInfo ) {
        list($name, $value) = $headerInfo;
        header($name . ": " . $value);
    }
    if ( $response->httpResponseCode() !== null ) {
        $code = $response->httpResponseCode();
        http_response_code($code);
    }
    print $formattedResponse;
} catch ( Exception $e ) {
    if (! extension_loaded('xdebug')) {
        $response = RestResponse::factory(
            false,
            $e->getMessage()
        );
        print "<pre>" . print_r($response, 1) . "</pre>";
    }
}
?>
