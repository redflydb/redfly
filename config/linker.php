<?php
// Configure the include path, set up the autoloader, etc.
// Bootstrap with the new bootstrap.php file.
require_once(__DIR__ . "/../lib/bootstrap.php");
// Set up the include path
$include_path  = ini_get("include_path") .
    ":" . $GLOBALS["options"]->general->site_base_dir . "/lib" .
    ":" . $GLOBALS["options"]->general->site_base_dir . "/lib/api";
ini_alter(
    "include_path",
    $include_path
);
// Include libraries that will always be needed
require_once("DbService.php");
// Set up the session.
// This must happen after the autoloader is defined in case any objects are stored in the session.
if ( php_sapi_name() !==  "cli" ) {
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
}
// Constant for the styling of the REDfly logo.
define(
    "HTML_REDFLY_LOGO",
    "<font face=\"verdana\"><i><b><font color=\"#be1e2d\">RED</font></b>fly</font></i>"
);
?>
