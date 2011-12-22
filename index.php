<?php
global $current_language,$current_user;
$mypath="modules/$currentModule";
include_once "$mypath/processConfig.php";
include_once "$mypath/vtapps/baseapp/vtapp.php";
?>
<link href="<?php echo $mypath; ?>/styles/evvtapps.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $mypath; ?>/styles/kendo.common.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $mypath; ?>/styles/kendo.default.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $mypath; ?>/styles/tipsy.css" rel="stylesheet" type="text/css" />
<script src="<?php echo $mypath; ?>/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/js/kendo.all.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/js/jquery.tipsy.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/js/evvtapps.js" type="text/javascript"></script>
<div id="evvtCanvas" class="evvtCanvas">
<?php
if (is_admin($current_user))
	$rsapps=$adb->query('select evvtappsid from vtiger_evvtapps where evvtappsid!=1 order by evvtappsid');
else
	$rsapps=$adb->pquery('select evvtappsid from vtiger_evvtapps
	 inner join vtiger_evvtappsuser on appid=evvtappsid
	 where userid=? and wenabled and evvtappsid!=1 order by sortorder',array($current_user->id));
$numapps=$adb->num_rows($rsapps);
for ($app=0;$app<$numapps;$app++) {
	$appid=$adb->query_result($rsapps,$app,'evvtappsid');
	$loadedclases=get_declared_classes();
	include_once "$mypath/vtapps/app$appid/vtapp.php";
	$newclass=array_diff(get_declared_classes(), $loadedclases);
	$newclass=array_pop($newclass);
	$newApp=new $newclass($appid);
	$rswincfg=$adb->query("select wvisible,canshow from vtiger_evvtappsuser where appid=$appid and userid=".$current_user->id);
	if ($adb->num_rows($rswincfg)>0) {
		$wincfg=$adb->fetch_array($rswincfg);
		$visible=$wincfg['wvisible'];
		$canshow=$wincfg['canshow'];
	} else {
		$visible=1;
		$canshow=1;
	}
	$divid="evvtapp$appid";
	$windiv="<div id='$divid' class='evvtappbox'
	       title='<b>".$newApp->getAppName($current_language)."</b><br>".$newApp->getTooltipDescription($current_language)."' ";
	if ($canshow==1) {
	$windiv.=" onclick='evvtappsOpenWindow($appid,\"$newclass\",".$newApp->getAppInfo($current_language).','.$newApp->getEditInfo($current_language).")'";
	}
	$windiv.="><img src='".$newApp->getAppIcon()."'></div>";
	echo $windiv.'<script language="javascript">';
	echo "$('#$divid').tipsy($tipsy_settings);";
	if ($newApp->canDelete()) {
		echo "$('#$divid').kendoDraggable({
                        hint: function() {
                        	var imgclone=$('#$divid').clone();
                        	imgclone.css({margin: -40});
                            return imgclone;
                        }});";
	}
	if ($canshow==0) {
		echo $newApp->getNoShowContent($current_language);
	}
	if ($visible==1) { // Open the visible widgets for the current user
	echo "evvtappsOpenWindow($appid,'$newclass',".$newApp->getAppInfo($current_language).','.$newApp->getEditInfo($current_language).');';
	}
	echo '</script>';
}
// Now we do Trash Can, always at the end
$numdel=$adb->getone("select count(*) from vtiger_evvtappsuser where wenabled and candelete and userid=".$current_user->id);
if (is_admin($current_user) or $numdel>0) {
include_once "$mypath/vtapps/app1/vtapp.php";
$newApp=new vtAppcomTSolucioTrash(1);
?>
<div id='evvtapptrash' class='evvtappbox' title='<b><?php echo $newApp->getAppName($current_language); ?></b><br><?php echo $newApp->getTooltipDescription($current_language); ?>'><img src='<?php echo $newApp->getAppIcon(); ?>'></div>
<script language="javascript">
$("#evvtapptrash").tipsy(<?php echo $tipsy_settings; ?>);
$("#evvtapptrash").kendoDropTarget({
	drop: droptargetTrashApp
});
</script>
<?php } ?>
</div> <!-- evvtCanvas -->
<script language="javascript">
$(window).unload( unloadCanvas );
</script>