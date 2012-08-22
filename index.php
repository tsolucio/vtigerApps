<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/
$mypath="modules/$currentModule";
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