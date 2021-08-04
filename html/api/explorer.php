<?php
// --------------------------------------------------------------------------------
// Query the API information url and present the user with a list of help links
// for each function and action.
// --------------------------------------------------------------------------------
require_once(dirname(__FILE__) . "/../../config/linker.php");
$urlBase = $GLOBALS["options"]->general->site_base_url;
$apiBase = $GLOBALS["options"]->rest->base_url;
$fullApiUrl = $urlBase . $apiBase . "/text";
$apiInformationUrl = $GLOBALS["options"]->general->site_base_url . $apiBase .
    "/json/information/api";
$exportInformationUrl = $GLOBALS["options"]->general->site_base_url . $apiBase .
    "/json/information/download";
$apiList = "";
$downloadHandlerHelp = "";
$cUrlSession = curl_init($apiInformationUrl);
curl_setopt(
    $cUrlSession,
    CURLOPT_HEADER,
    false
);
curl_setopt(
    $cUrlSession,
    CURLOPT_RETURNTRANSFER,
    true
);
$result = curl_exec($cUrlSession);
$httpCode = curl_getinfo(
    $cUrlSession,
    CURLINFO_HTTP_CODE
);
if ( ! $result ) {
    print "<br>Error: " . curl_error($cUrlSession);
    curl_close($cUrlSession);
    exit();
} else if ( $httpCode > 400 ) {
    print "<br>Error: HTTP Response " . $httpCode . "<br>" .
        $apiInformationUrl . "\n";
    curl_close($cUrlSession);
    exit();
}
// --------------------------------------------------------------------------------
// Get the list of available APIs
// --------------------------------------------------------------------------------
$information = json_decode($result);
if ( $information->success !== 1 ) {
    print "<br>Error accessing the API information at \"" . $apiInformationUrl .
        "\" : " . $information->message;
}
else {
    $results = (array) $information->results;
    ksort($results);
    foreach ( $results as $entity => $actionList ) {
        $apiList .= "<ul>\n";
        foreach ( $actionList as $action ) {
            $displayUrl = $apiBase . "/text/". $entity . "/" . $action;
            $apiList .= "<li> <a href=\"" . $fullApiUrl . "/" . $entity .
                "/". $action . "?help=y\" " .
                "target=\"help\">" . $displayUrl . "</a> (<a href=\"" . 
                $fullApiUrl . "/" . $entity . "/" . $action . "\" " .
                "target=\"_blank\">Test</a>) </li>\n";
        }
        $apiList .= "</ul>\n";
    }
}
curl_close($cUrlSession);
$cUrlSession = curl_init($exportInformationUrl);
curl_setopt(
    $cUrlSession,
    CURLOPT_HEADER,
    false
);
curl_setopt(
    $cUrlSession,
    CURLOPT_RETURNTRANSFER,
    true
);
$result = curl_exec($cUrlSession);
$httpCode = curl_getinfo(
    $cUrlSession,
    CURLINFO_HTTP_CODE
);
if ( ! $result ) {
    print "<br>Error: " . curl_error($cUrlSession);
    curl_close($cUrlSession);
    exit();
} else if ( $httpCode > 400 ) {
    print "<br>Error: HTTP Response " . $httpCode . "<br>" .
        $apiInformationUrl. "\n";
    curl_close($cUrlSession);
    exit();
}
// --------------------------------------------------------------------------------
// Get the list of available download/export formats
// --------------------------------------------------------------------------------
$information = json_decode($result);
if ( $information->success !== 1 ) {
    print "<br>Error accessing the API information at \"" . $exportInformationUrl.
        "\" : " . $information->message;
}
else {
    $downloadHandlerHelp = (array) $information->results;
    ksort($downloadHandlerHelp);
}
curl_close($cUrlSession);
$availableReturnFormats = array();
$response = RestResponse::factory(false, "");
$methodList = get_class_methods("RestResponse");
foreach ( $methodList as $method ) {
    if ( substr($method, -6) === "Format" ) {
        $formatName = substr(
            $method,
            0,
            strlen($method) - 6
        );
        $helpMethod = $formatName . "Help";
        if ( in_array($helpMethod, $methodList) ) {
            eval("\$helpMessage = \$response->" . $helpMethod . "();");
        } else {
            $helpMessage = null;
        }
        $availableReturnFormats[$formatName] = $helpMessage;
    }
}
?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=10.0, user-scalable=yes">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>REDfly API Explorer</title>
</head>
<body>
    <div align="center"><b>REDfly API Explorer</b></div>
    <br>
    <table border=0>
        <tr>
            <th width=375> API Url </th>
            <th> API Help </th>
        </tr>
        <tr>
            <td valign="top"> <?= $apiList ?> </td>
            <td valign="top"> <iframe name="help" width="750" height="500" scrolling="yes"></iframe> </td>
        </tr>
        <tr>
            <td colspan=2>
                <br> <b>Notes</b><hr>
                <p>
                API actions are broken down into the following general categories:
                <ul>
                    <li> <b>list</b> - Return a list of one or more entities with optional filters
                    <li> <b>search</b> - Search the database based on criteria provided and return a subset of the entity&#39;s fields.  This is used for more complex entities such as binding sites and reporter constructs where it&#39;s necessary to display a subset of the informaiton and drill down.
                    <li> <b>get</b> - Retrieve a single entity, typically using an id
                </ul>
                </p>
                <p>
                <b>API Url format</b><hr>
                <br> /api/rest/<i>{return_format}</i>/<i>{entity}</i>/<i>{action}</i>[?opt1=value1&opt2=value2...]<br>
                <br> <i>help=y</i> is always an available option and returns help info if available.  Other options are specific to the particular API call.<br>
                <br> Available options for <i>{return_format}</i> are: <b><?= implode("</b>, <b> ", array_keys($availableReturnFormats)) ?></b>
                <br> Where:
                <ul>
                    <?php
                        foreach ( $availableReturnFormats as $format => $help ) {
                            print "<li><b>" . $format . "</b> - " . htmlentities($help) . "</li\n";
                        }
                    ?>
                </ul>
                Examples
                <ul>
                    <li> Get the list of chromosomes in text format (for debugging): /api/rest/text/chromosome/list </li>
                    <li> Get the first 20 genes sorted by name in JSON format: /api/rest/json/gene/list?limit=20&amp;sort=name </li>
                    <li> Get the reporter construct with redfly id RFRC:00000168.001: /api/rest/json/reporterconstruct/get?redfly_id=RFRC:00000168.001 </li>
                    <li> Search the binding sites for all matches on a pubmed id: /api/rest/json/transcriptionfactorbindingsite/search?pmid=8078474 </li>
                </ul>
                </p>
                <br><b>Export File Formats And Options (used by the /api/rest/raw/download/* API handler)</b><hr>
                <p>
                For example: /api/rest/raw/download/list?format=fasta&filename=dump&fasta_seq=both
                <ul>
                    <?php
                        foreach ( $downloadHandlerHelp as $exportFormat => $information ) {
                            print "<li><b>" . $exportFormat . "</b> - " . $information->message . "\n";
                            if ( $information->results !== null ) {
                                print "<ul>\n";
                                foreach ( $information->results as $key => $value ) {
                                    print "<li> " . $key . " - " . $value . "\n";
                                }
                                print "</ul>\n";
                            }
                            print "</li><br>\n";
                        }
                    ?>
                </ul>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
