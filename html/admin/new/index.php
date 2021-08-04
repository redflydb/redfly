<?php require_once __DIR__ . '/../../login.php'; ?>
<!DOCTYPE HTML>
<html manifest="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=10.0, user-scalable=yes">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>REDfly user: <?= Auth::getUser()->username() ?> (<?
        if ( Auth::getUser()->hasRole("admin") ) {
            print "admin"; }
        else {
            print "curator";
        } ?> role) </title>
    <!--
    <script type="text/javascript">
        var Ext = Ext || {}; // Ext namespace won't be defined yet...
        // This function is called by the Microloader after it has performed basic
        // device detection. The results are provided in the "tags" object. You can
        // use these tags here or even add custom tags. These can be used by platform
        // filters in your manifest or by platformConfig expressions in your app.
        Ext.beforeLoad = function (tags) {
            var s = location.search,  // the query string (ex "?foo=1&bar")
                profile;
            // For testing look for "?classic" or "?modern" in the URL to override
            // device detection default.
            //
            if (s.match(/\bclassic\b/)) {
                profile = 'classic';
            }
            else if (s.match(/\bmodern\b/)) {
                profile = 'modern';
            }
            else {
                profile = tags.desktop ? 'classic' : 'modern';
                //profile = tags.phone ? 'modern' : 'classic';
            }
            Ext.manifest = profile; // this name must match a build profile name
            // This function is called once the manifest is available but before
            // any data is pulled from it.
            //return function (manifest) {
                // peek at / modify the manifest object
            //};
        };
    </script>
    -->
    <!-- The lines below write the Javascript declaration of global variables to bear the 
       predefined state based on the current user role so that it can be accessible for 
       the 'closed' ExtJS 6 application going to be built by Sencha Cmd in the next line. -->
    <?php
    print "<script type=\"text/javascript\">" . PHP_EOL;
    if ( Auth::getUser()->hasRole("admin") ) {
        print "    var role = 'admin';" . PHP_EOL;
        print "    var adminRole = true;" . PHP_EOL;
        print "    var predefinedState = 'approval';" . PHP_EOL;
        print "    var predefinedStateIndex = 0;" . PHP_EOL;
    }
    else {
        if ( Auth::getUser()->hasRole("curator") ) {
            print "     var role = 'curator';" . PHP_EOL;
            print "     var adminRole = false;" . PHP_EOL;
            print "     var predefinedState = 'editing';" . PHP_EOL;
            print "     var predefinedStateIndex = 3;" . PHP_EOL;
        }
    }
    print "    var crmSegmentErrorMargin = " . $GLOBALS["options"]->crm_segment->error_margin . ";" . PHP_EOL;
    print "    var predictedCrmErrorMargin = " . $GLOBALS["options"]->predicted_crm->error_margin . ";" . PHP_EOL;
    print "    var rcErrorMargin = " . $GLOBALS["options"]->rc->error_margin . ";" . PHP_EOL;
    print "    var tfbsErrorMargin = " . $GLOBALS["options"]->tfbs->error_margin . ";" . PHP_EOL;
    print "    </script>" . PHP_EOL;
    ?>
    <!-- The line below must be kept intact for Sencha Cmd to build your application -->
    <script id="microloader" data-app="e2d4b87a-13c2-4638-9b62-bb92f392875b" type="text/javascript" src="bootstrap.js"></script>
</head>
<body></body>
</html>
