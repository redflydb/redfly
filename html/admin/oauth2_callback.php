<?php
require_once(__DIR__ . "/../../lib/bootstrap.php");
// Create the client object and set the authorization configuration
// from the OAuth2 credentials file you downloaded from the Developers Console.
$client = new Google_Client();
$client->setAuthConfig(__DIR__ . "/../../config/" . $GLOBALS["options"]->google_applications->oauth2_credentials_file);
$client->setRedirectUri("http://" . $_SERVER["HTTP_HOST"] . "/admin/oauth2_callback.php");
$client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);
// Handle authorization flow from the server.
if ( ! isset($_GET["code"]) ) {
    $auth_url = $client->createAuthUrl();
    header("Location: " . filter_var(
        $auth_url,
        FILTER_SANITIZE_URL
    ));
} else {
    $client->authenticate($_GET["code"]);
    $_SESSION["access_token"] = $client->getAccessToken();
    $redirect_uri = "http://" . $_SERVER["HTTP_HOST"] . "/admin/google_analytics.php";
    header("Location: " . filter_var(
        $redirect_uri,
        FILTER_SANITIZE_URL
    ));
}
