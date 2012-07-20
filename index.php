<?php
$mypath="modules/$currentModule";
include_once "$mypath/config.php";
include "$mypath/language/$current_language.lang.php";
?>
<link href="<?php echo $mypath; ?>/styles/evvtapps.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $mypath; ?>/kendoui/styles/kendo.common.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $mypath; ?>/kendoui/styles/kendo.default.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $mypath; ?>/styles/tipsy.css" rel="stylesheet" type="text/css" />
<script src="<?php echo $mypath; ?>/kendoui/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/kendoui/js/kendo.web.min.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/js/jquery.tipsy.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/js/evvtapps.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/jquery-ui/js/jquery-ui-1.8.21.custom.min.js" type="text/javascript"></script>
<div id="evvtCanvas" class="evvtCanvas">
<ul id="launchers"></ul>
</div> <!-- evvtCanvas -->