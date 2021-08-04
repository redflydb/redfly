<?php
require_once(dirname(__FILE__) . "/../config/linker.php");
Auth::logout();
include("header.php");
print "<br><br> You have successfully logged out.\n";
include("footer.php");
?>
