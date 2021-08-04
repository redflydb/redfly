<?php
require_once(dirname(__FILE__) . "/../config/linker.php");
$baseURL = $GLOBALS["options"]->general->site_base_url;
$dbInformationUrl = $baseURL . "/api/rest/json/information/db";
$cUrlSession = curl_init($dbInformationUrl);
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
if ( $result === false ) {
    print "<br> Error connecting to '" . $dbInformationUrl . "': " . curl_error($cUrlSession);
    curl_close($cUrlSession);
    exit();
}
$decode = json_decode($result);
$information = array_shift($decode->results);
$numberCrms = $information->number_crms;
$numberCrmsInVivo = $information->number_crms_in_vivo;
$numberCrmsNonInVivoHavingNoCellCulture = $information->number_crms_non_in_vivo_having_no_cell_culture;
$numberCrmsCellCultureOnly = $information->number_crms_cell_culture_only;
$numberCrmGenes = $information->number_crm_genes;
$numberCrmSegments = $information->number_crmsegments;
$numberCrmSegmentGenes = $information->number_crmsegment_genes;
$numberPredictedCrms = $information->number_predictedcrms;
$numberTfbss = $information->number_tfbss;
$numberTfbsGenes = $information->number_tfbs_genes;
$numberTfbsTfs = $information->number_tfbs_tfs;
$numberPublications = $information->number_publications;
$lastUpdateTime = date(
    "m/d/Y",
    max(
        $information->last_rc_update,
        $information->last_crmsegment_update,
        $information->last_tfbs_update
    )
);
$numberLastUpdate = 0;
if ( date(
    "m/d/Y",
    $information->last_rc_update
) === $lastUpdateTime ) {
    $numberLastUpdate += $information->number_last_rc_update;
}
if ( date(
    "m/d/Y",
    $information->last_crmsegment_update
) === $lastUpdateTime ) {
    $numberLastUpdate += $information->number_last_crmsegment_update;
}
if ( date(
    "m/d/Y",
    $information->last_tfbs_update
) === $lastUpdateTime ) {
    $numberLastUpdate += $information->number_last_tfbs_update;
}
$lastUpdateLine = "Database updated on " . $lastUpdateTime . " with " . $numberLastUpdate . " change" . ($numberLastUpdate > 1 ? "s" : "");
?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=10.0, user-scalable=yes">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Redfly Database</title>
    <link rel="icon" href="<?= $baseURL ?>images/favicon.png" type="image/gif">
    <link href="<?= $baseURL ?>css/redfly.css" type="text/css" rel="stylesheet">
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <?php
    if ( isset($GLOBALS["options"]->google_applications->analytics_api_3_tracking_id) ) {
        print "<script type=\"text/javascript\" async src=\"https://www.googletagmanager.com/gtag/js?id=" .
            $GLOBALS["options"]->google_applications->analytics_api_3_tracking_id . "\"></script>";
    }
    ?>
    <script type="text/javascript">
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        <?php
            if ( isset($GLOBALS["options"]->google_applications->analytics_api_3_tracking_id) ) {
                print "gtag('config', '" . $GLOBALS["options"]->google_applications->analytics_api_3_tracking_id . "');";
            }        
            if ( isset($GLOBALS["options"]->google_applications->analytics_4_measurement_id) ) {
                print "gtag('config', '" . $GLOBALS["options"]->google_applications->analytics_4_measurement_id . "');";
            }
        ?>
    </script>
    <script type="text/javascript" src="<?= $baseURL ?>js/reminder_functions.js"></script>
</head>
<body onLoad="setReminderTimer(10000);">
<!-- Start Header -->
<header class="header">
    <div class="main_logo"><img src="<?= $baseURL ?>images/RedFlyLogo-170x120.png"></div>
    <div><p class="version">
        v<?= $GLOBALS["options"]->general->redfly_version . " (Database Updated " . $lastUpdateTime . ")" ?>
    </p></div>
    <div class="lastupdate">
      <?= $lastUpdateLine ?>
      <br>
      Gene Annotation Version <?= $GLOBALS["options"]->ontologies->gene_annotation_version ?>
      <br>
      Anatomy Ontology Version <?= $GLOBALS["options"]->ontologies->anatomy_ontology_update_date ?>
    </div>
</header>
<nav class="menu" id="navbar">
  <ul class="menu_items">
    <li><a class="nav_effect"
           href="<?= $baseURL ?>index.php">Home</a></li>
    <li><a class="nav_effect"
           href="<?= $baseURL ?>species_information.php">Species</a></li>
    <li><a class="nav_effect"
           href="<?= $baseURL ?>search.php"
           onclick="document.location.reload(true)">Search</a></li>
    <li><a class="nav_effect"
           href="<?= $baseURL ?>help.php">Help</a></li>
    <li><a class="nav_effect"
           href="<?= $baseURL ?>resources.php">Resources/Links</a></li>
    <li><a class="nav_effect"
           href="<?= $baseURL ?>news_list.php">News</a></li>
    <li><a class="nav_effect"
           href="<?= $baseURL ?>about_redfly.php">About REDfly</a></li>
    <li><a class="nav_effect"
           href="<?= $baseURL ?>contact.php"
           onclick="document.location.reload(true)">Contact Us</a></li>
  </ul>
</nav>
<hr class="redline">
<!-- End Header -->
<div align="center">
<table width="95%">
<tr>
  <td>
  <!-- Start Main Content -->
    <div class="welcome">Welcome to <?= $GLOBALS["options"]->general->environment_name ?> <?= HTML_REDFLY_LOGO ?>
      <div class="tagline">Regulatory Element Database for <i>Drosophila</i> and other insects v<?= $GLOBALS["options"]->general->redfly_version ?></div>
    </div>
