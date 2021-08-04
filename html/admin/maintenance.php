<?php require_once(dirname(__FILE__) . "/../../config/linker.php"); ?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Redfly Database</title>
<link rel="icon" href="<?= $GLOBALS["options"]->general->site_base_url ?>/images/favicon.png" type = "image/gif">
<link href="<?= $GLOBALS["options"]->general->site_base_url ?>/css/redfly.css" type="text/css" rel="stylesheet">
</head>
<body>
<div align="center">
<table>
  <tr>
    <td><img src="<?= $GLOBALS["options"]->general->site_base_url ?>/images/RedFlyLogo-170x120.png"> </td>
    <td class="tagline" align="left"><i>Regulatory Element Database for</i> Drosophila <i>v<?= $GLOBALS["options"]->general->redfly_version ?></i></td>
  </tr>
</table>
<hr class="redline">
<br>
<div class="heading_c"> Welcome to <?= HTML_REDFLY_LOGO ?> </div>
<div class="heading_release_c">
<p>
<?= HTML_REDFLY_LOGO ?> is currently undergoing maintenance and upgrades. Please check back shortly.
</p>
</div>
