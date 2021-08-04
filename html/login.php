<?php
/**
 * Authenticates an user via HTTP Basic Authentication.
 * This file uses the /login endpoint of the API via cURL to authenticate an
 * user using HTTP Basic Authentication.
 * Any page that "require"s this file will require the user to log in 
 * before being allowed to view the page.
 */
require_once __DIR__ . "/../config/linker.php";
if ( isset($_SERVER["PHP_AUTH_USER"]) ) {
    $cUrlSession = curl_init();
    $url = $GLOBALS["options"]->general->site_base_url . $GLOBALS["options"]->rest->base_url_v2 . "/admin/login";
    curl_setopt(
        $cUrlSession,
        CURLOPT_URL,
        $url
    );
    curl_setopt(
        $cUrlSession,
        CURLOPT_USERPWD,
        $_SERVER["PHP_AUTH_USER"] . ":" . $_SERVER["PHP_AUTH_PW"]
    );
    curl_setopt(
        $cUrlSession,
        CURLOPT_RETURNTRANSFER,
        true
    );
    $result = json_decode(
        curl_exec($cUrlSession),
        true
    );
    curl_close($cUrlSession);
    if ( isset($result["success"]) && 
        ($result["success"] === true) ) {
        Auth::authenticate();
    } else {
        show_login_prompt();
    }
} else {
    show_login_prompt();
}
/**
 * Prompts the browser the show the HTTP Basic Authentication dialog by setting
 * the appropriate HTTP headers.
 */
function show_login_prompt(): void
{
    header("WWW-Authenticate: Basic realm=\"" . $GLOBALS["options"]->general->site_auth_realm . "\"");
    header("HTTP/1.0 401 Unauthorized");
    echo "You must be authenticated to view this page.";
    exit;
}
