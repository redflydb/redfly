<?php include("header.php"); ?>
<div class="heading_release_c">
</div>
<link href="js/extjs/resources/css/ext-all.css" type="text/css" rel="stylesheet">
<link href="js/extjs/resources/css/xtheme-gray.css" type="text/css" rel="stylesheet">
<link href="js/extjs/examples/shared/examples.css" type="text/css" rel="stylesheet">
<link href="css/ext-css-override.css" type="text/css" rel="stylesheet">
<link href="css/xtheme-gray-override.css" type="text/css" rel="stylesheet">
<link href="css/redfly.css" type="text/css" rel="stylesheet">
<style type="text/css">
    .x-grid3-row-over .x-grid3-cell-inner {
        font-weight: bold;
    }
</style>
<script type="text/javascript" src="js/extjs/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="js/extjs/ext-all.js"></script>
<script type="text/javascript" src="js/BufferView.js"></script>
<script language="JavaScript">
var redflyApiUrl = "<?= $GLOBALS["options"]->rest->base_url ?>";
var redflyApiV2Url = "<?= $GLOBALS["options"]->rest->base_url_v2 ?>";
var redflyBaseUrl = "<?= $GLOBALS["options"]->general->site_base_url ?>";
var redflyAnatomyOntologyUpdateDate = "<?= $GLOBALS["options"]->ontologies->anatomy_ontology_update_date ?>";
var redflyDevelopmentOntologyUpdateDate = "<?= $GLOBALS["options"]->ontologies->development_ontology_update_date ?>";
var goOntologyUpdateDate = "<?= $GLOBALS["options"]->ontologies->go_ontology_update_date ?>";
</script>
<script type="text/javascript" src="js/configuration.js"></script>
<script type="text/javascript" src="js/utility_functions.js"></script>
<script type="text/javascript" src="js/search_widgets.js"></script>
<script type="text/javascript" src="js/search_components.js"></script>
<script type="text/javascript" src="js/search_form.js"></script>
<script type="text/javascript" src="js/search_help.js"></script>
<script type="text/javascript" src="js/rc_display_windows.js"></script>
<script type="text/javascript" src="js/crm_segment_display_windows.js"></script>
<script type="text/javascript" src="js/predicted_crm_display_windows.js"></script>
<script type="text/javascript" src="js/tfbs_display_windows.js"></script>
<!-- ExtJs classes will go into these 2 div tags -->
<div id="search"></div>
<div id="grid-example"></div>
<?php include("footer.php"); ?>
