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
$defaultcanvas = $adb->getone('select defaultcanvas from vtiger_evvtappscanvas where userid='.$current_user->id);
if (empty($defaultcanvas)) $defaultcanvas='windows';
?>
<script type="text/javascript">
	var evvtcanvas = '<?php echo $evvtcanvas; ?>';
	var defaultcanvas = '<?php echo $defaultcanvas; ?>';
</script>
<link href="<?php echo $mypath; ?>/styles/evvtapps.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $mypath; ?>/kendoui/styles/kendo.common.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $mypath; ?>/kendoui/styles/kendo.default.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $mypath; ?>/styles/tipsy.css" rel="stylesheet" type="text/css" />
<script src="<?php echo $mypath; ?>/kendoui/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/kendoui/js/kendo.web.min.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/js/jquery.tipsy.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/js/evvtapps.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/jquery-ui/js/jquery-ui-1.8.21.custom.min.js" type="text/javascript"></script>
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
  	<img src="<?php echo $mypath; ?>/images/<?php echo ($defaultcanvas=='windows' ? 'selectedcanvas' : 'blank'); ?>.png" id="defaultcanvasimg1"><img src="<?php echo $mypath; ?>/images/<?php echo ($defaultcanvas=='dashboard' ? 'selectedcanvas' : 'blank'); ?>.png" id="defaultcanvasimg2"><img src="<?php echo $mypath; ?>/images/<?php echo ($defaultcanvas=='allapps' ? 'selectedcanvas' : 'blank'); ?>.png" id="defaultcanvasimg3">
    <a href="javascript:void(0);" id="evvthcwin" onClick="makeContent('windows')" <?php if ($evvtcanvas=='windows') echo 'class="evvtheaderCenterActive"'; ?>><?php echo getTranslatedString('Windows',$currentModule); ?></a>
    <a href="javascript:void(0);" id="evvthcdsh" onClick="makeContent('dashboard')" <?php if ($evvtcanvas=='dashboard') echo 'class="evvtheaderCenterActive"'; ?>><?php echo getTranslatedString('Dashboard',$currentModule); ?></a>
    <a href="javascript:void(0);" id="evvthcapp" onClick="makeContent('allapps')" <?php if ($evvtcanvas=='allapps') echo 'class="evvtheaderCenterActive"'; ?>><?php echo getTranslatedString('Applications',$currentModule); ?></a>
  </div>
</div>
<div id="evvtheaderhide"><img src="<?php echo $mypath; ?>/images/hidepanel.png" id="evvtheaderhideimage" onClick="javascript:evvtHeaderToggle();"/><img src="<?php echo $mypath; ?>/images/hideallpanel.png" id="evvtheaderhideallimage" onClick="javascript:evvtHeaderToggleAll();" style="margin-left: 5px;" /></div>
</div>
<div id="evvtleftButton"<?php if ($evvtcanvas!='allapps') echo ' style="display:none;"'; ?>><input type="button" value="<" onClick="move2NextApp(-1)"></div>
<div id="evvtCanvas" class="evvtCanvas"><ul id="launchers"></ul></div>
<div id="evvtDashboardCanvas" style='display:none;width:100%;height:610px;margin:auto'>
  <div id="evvtDashboardDesigner" style="height: 100%;">
    <div id="evvtDashboardLayout" style="height: 100%; width: 80%;"></div>
    <div id="evvtDashboardEditor" style="height: 800px; width: 40%;">
      <div class="evvtDashboardEditorpane-content">
       <span class="evvtDashboardEditorMenuTop"><div style="float:left">&nbsp;</div>
        <div id="evvtDBETAddRowLayout" class="evvtDashboardEditorTool evvtDashboardEditorToolAddRowLayout evvtDashboardEditorToolUnactive"/></div>
        <div id="evvtDBETAddvtAppCell" class="evvtDashboardEditorTool evvtDashboardEditorToolAddvtAppCell evvtDashboardEditorToolUnactive"/></div>
        <div id="evvtDBETRefresh" class="evvtDashboardEditorTool evvtDashboardEditorToolRefresh evvtDashboardEditorToolUnactive"/></div>
        <div id="evvtDBETCollapseExpand" class="evvtDashboardEditorTool evvtDashboardEditorToolCollapseExpand evvtDashboardEditorToolActive"/></div>
        <div id="evvtDBETSave" class="evvtDashboardEditorTool evvtDashboardEditorToolSave evvtDashboardEditorToolUnactive"/></div>
       </span>
       <div id="evvtDashboardEditorTreeview" style="width:100%"></div>
       <script id="evvtDashboardEditorTreeview-template" type="text/kendo-ui-template">
            #= item.text #
			<span id="evvtdbappdata-#= item.id #" atributos="evvtinfo" splitprops='#= item.splitprops #'>&nbsp;&nbsp;</span>
            <a href='javascript:void(0);'></a>
       </script>
      </div>
      <div class="evvtDashboardEditorpane-content">
       <span  class="evvtDashboardEditorMenuTop"><?php echo getTranslatedString('MenuAppProps',$currentModule); ?><input type="hidden" id="evvteditingdiv"/></span>
       <div id="evvtDashboardEditorPropview" style="width:100%">
       <div id="evvtDashboardEditorProps" style="width:100%">
       	<div id="evvtSplitvtAppidDiv" class="evvtPropertyItem">
			<div class="evvtPropertycol1"><?php echo getTranslatedString('splitvtAppid',$currentModule); ?></div>
			<div class="evvtPropertycol2"><input id="evvtSplitvtAppid" type="hidden" /></div>
		</div>
       	<div class="evvtPropertyItem">
			<div class="evvtPropertycol1"><?php echo getTranslatedString('splitSize',$currentModule); ?></div>
			<div class="evvtPropertycol2"><input id="evvtSplitSize" type="number" size=4 value="" /></div>
		</div>
       	<div class="evvtPropertyItem">
			<div class="evvtPropertycol1"><?php echo getTranslatedString('splitMax',$currentModule); ?></div>
			<div class="evvtPropertycol2"><input id="evvtSplitMax" type="number" size=4 value="" /></div>
		</div>
       	<div class="evvtPropertyItem">
			<div class="evvtPropertycol1"><?php echo getTranslatedString('splitMin',$currentModule); ?></div>
			<div class="evvtPropertycol2"><input id="evvtSplitMin" type="number" size=4 value="" /></div>
		</div>
       	<div class="evvtPropertyItem">
			<div class="evvtPropertycol1"><?php echo getTranslatedString('splitResize',$currentModule); ?></div>
			<div class="evvtPropertycol2"><input id="evvtSplitResize" type="checkbox" onchange="javascript:doResizeChange(this.checked);" /></div>
		</div>
       	<div class="evvtPropertyItem">
			<div class="evvtPropertycol1"><?php echo getTranslatedString('splitScroll',$currentModule); ?></div>
			<div class="evvtPropertycol2"><input id="evvtSplitScroll" type="checkbox" onchange="javascript:doScrollChange(this.checked);" /></div>
		</div>
		<div class="evvtPropertyItem">
			<div class="evvtPropertycol1"><?php echo getTranslatedString('splitCollapsed',$currentModule); ?></div>
			<div class="evvtPropertycol2"><input id="evvtSplitCollapsed" type="checkbox" onchange="javascript:doCollapsedChange(this.checked,true);" /></div>
		</div>
       	<div class="evvtPropertyItem">
			<div class="evvtPropertycol1"><?php echo getTranslatedString('splitCollapsable',$currentModule); ?></div>
			<div class="evvtPropertycol2"><input id="evvtSplitCollapsable" type="checkbox" onchange="javascript:doCollapsibleChange(this.checked);" /></div>
		</div>
       </div>
      </div>
    </div>
  </div>
</div>
</div>
<div id="evvtrightButton"<?php if ($evvtcanvas!='allapps') echo ' style="display:none;"'; ?>><input type="button" value=">" onClick="move2NextApp(1)"></div>
<div style="clear:both"></div>
