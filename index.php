<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/
$mypath="modules/$currentModule";
include "$mypath/language/$current_language.lang.php";
$skipFooters=true;
$evvtcanvas = vtlib_purify($_REQUEST['evvtapps_canvas']);
$evvtcanvas = (empty($evvtcanvas) ? 'windows' : $evvtcanvas);
?>
<script type="text/javascript">var evvtcanvas = '<?php echo $evvtcanvas; ?>';</script>
<link href="<?php echo $mypath; ?>/styles/evvtapps.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $mypath; ?>/kendoui/styles/kendo.common.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $mypath; ?>/kendoui/styles/kendo.default.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $mypath; ?>/styles/tipsy.css" rel="stylesheet" type="text/css" />
<script src="<?php echo $mypath; ?>/kendoui/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/kendoui/js/kendo.web.min.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/kendoui/js/kendo.upload.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/js/jquery.tipsy.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/js/evvtapps.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/jquery-ui/js/jquery-ui-1.8.21.custom.min.js" type="text/javascript"></script>
<!-- input id="vtappDDListInput" type="hidden" style="float:right;position:absolute;top:120px;right:10px"/  -->
<div id="vtappDDListDiv" style="float:right;position:absolute;top:120px;right:10px;z-index:10000;">
<input id="vtappDDListInput" type="hidden" size=1/>
</div>
<img id="vtappStatus" src="<?php echo $mypath; ?>/images/ajax-loader.gif" style="display:none;float:right;position:absolute;top:90px;right:60px;z-index:10000;"/>
<div id="evvtAppsAboutUs"></div>
<div id= "evvtheadercontainer">
<div id="evvtheader">
  <div id="evvtheaderLeft"><img src="<?php echo $mypath; ?>/images/evolutivo.png" id="evvtAboutUsImage" onClick="javascript:evvtShowAboutUs();"/></div>
  <div id="evvtheaderRight"><div id="evvtHeaderDesc"></div><div style="float:right;height:45px;" onClick="javascript:jumpToMenu();"><img src="<?php echo $mypath; ?>/images/showpanel.png" id="evvtHeaderJumpTo" style="display:none;"/></div><div style="float:right;"><img src="<?php echo $mypath; ?>/images/blank.png" id="evvtHeaderImage"/></div></div>
  <div id="evvtheaderCenter">
    <a href="javascript:void(0);" id="evvthcwin" onClick="makeContent('windows')" <?php if ($evvtcanvas=='windows') echo 'class="evvtheaderCenterActive"'; ?>><?php echo getTranslatedString('Windows',$currentModule); ?></a>
    <a href="javascript:void(0);" id="evvthcdsh" onClick="makeContent('dashboard')" <?php if ($evvtcanvas=='dashboard') echo 'class="evvtheaderCenterActive"'; ?>><?php echo getTranslatedString('Dashboard',$currentModule); ?></a>
    <a href="javascript:void(0);" id="evvthcapp" onClick="makeContent('allapps')" <?php if ($evvtcanvas=='allapps') echo 'class="evvtheaderCenterActive"'; ?>><?php echo getTranslatedString('Applications',$currentModule); ?></a>
  </div>
</div>
<div id="evvtheaderhide"><img src="<?php echo $mypath; ?>/images/hidepanel.png" id="evvtheaderhideimage" onClick="javascript:evvtHeaderToggle();"/></div>
</div>
<div id="evvtleftButton"<?php if ($evvtcanvas!='allapps') echo ' style="display:none;"'; ?>><input type="button" value="<" onClick="move2NextApp(-1)"></div>
<div id="evvtCanvas" class="evvtCanvas"><ul id="launchers"></ul></div>
<div id="evvtrightButton"<?php if ($evvtcanvas!='allapps') echo ' style="display:none;"'; ?>><input type="button" value=">" onClick="move2NextApp(1)"></div>
<div style="clear:both"></div>