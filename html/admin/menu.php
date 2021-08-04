<?php
require_once(__DIR__ . "/../../config/linker.php");
require_once(__DIR__ . "/../login.php");
include("../header.php");
?>
<!-- Include Ext and app-specific scripts: -->
<script type="text/javascript" src="<?= $baseURL ?>js/extjs/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>js/extjs/ext-all.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>js/BufferView.js"></script>
<!-- Include Ext stylesheets here: -->
<!-- Use the grey theme instead of the default blue theme -->
<link rel="stylesheet" type="text/css" href="<?= $baseURL ?>js/extjs/resources/css/ext-all-notheme.css" />
<link rel="stylesheet" type="text/css" title="blue" href="<?= $baseURL ?>js/extjs/resources/css/xtheme-gray.css" />
<link rel="stylesheet" type="text/css" title="blue" href="<?= $baseURL ?>css/extjs-overrides.css" />
<!-- Include your own Javascript file here - adapt the filename to your filename-->
<script type="text/javascript" src="<?= $baseURL ?>admin/js/namespaces.js"></script>
<script type="text/javascript">
REDfly.config.baseUrl = "<?= $baseURL ?>";
REDfly.config.apiUrl = "<?= $baseURL . $GLOBALS["options"]->rest->base_url ?>";
REDfly.config.userName = "<?= Auth::getUser()->username() ?>";
REDfly.config.userFullName = "<?= Auth::getUser()->fullName() ?>";
REDfly.config.userId = <?= Auth::getUser()->userId() ?>;
REDfly.config.isAdmin = <?= ( Auth::getUser()->hasRole("admin") ? 1 : 0 ) ?>;
REDfly.config.crmSegmentErrorMargin = <?= $GLOBALS["options"]->crm_segment->error_margin ?>;
REDfly.config.predictedCrmErrorMargin = <?= $GLOBALS["options"]->predicted_crm->error_margin ?>;
REDfly.config.rcErrorMargin = <?= $GLOBALS["options"]->rc->error_margin ?>;
REDfly.config.tfbsErrorMargin = <?= $GLOBALS["options"]->tfbs->error_margin ?>;
</script>
<script type="text/javascript" src="<?= $baseURL ?>admin/js/common_components.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>admin/js/reporter_construct_components.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>admin/js/crm_segment_components.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>admin/js/search_components.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>admin/js/report_components.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>admin/js/dialog_user_profile.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>admin/js/dialog_rc.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>admin/js/dialog_rc_ts.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>admin/js/dialog_tfbs.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>admin/js/dialog_crm_segment.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>admin/js/dialog_crm_segment_ts.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>admin/js/dialog_approve_rc.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>admin/js/dialog_approve_tfbs.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>admin/js/dialog_approve_crm_segment.js"></script>
<script type="text/javascript" src="<?= $baseURL ?>admin/js/search.js"></script>
<div id="admintools"></div>
<?php include("../footer.php"); ?>
