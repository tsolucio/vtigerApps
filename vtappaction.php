<?php
$vtappaction=vtlib_purify($_REQUEST['vtappaction']);
$classname=vtlib_purify($_REQUEST['class']);
$appid=vtlib_purify($_REQUEST['appid']);
$return='';
if (!empty($classname) and !empty($vtappaction) and !empty($appid) and is_numeric($appid)) {
	global $adb,$current_language;
	$mypath="modules/$currentModule";
	include_once "$mypath/processConfig.php";
	include_once "$mypath/vtapps/baseapp/vtapp.php";
	include "$mypath/vtapps/app$appid/vtapp.php";
	$vtapp=new $classname($appid);
	switch ($vtappaction) {
		case 'getAbout':
			$return=$vtapp->getAbout($current_language);
			break;
		case 'getContent':
			$return=$vtapp->getContent($current_language);
			break;
		case 'getEdit':
			$return=$vtapp->getEdit($current_language);
			break;
		case 'doResize':
			$vtaWidth = vtlib_purify($_REQUEST['appwidth']);
			$vtaHeight= vtlib_purify($_REQUEST['appheight']);
			$return=$vtapp->doResize($current_language,$vtaWidth,$vtaHeight);
			break;
		case 'doShow':
			$vtapp->evvtSetVisible(1);
			$return=$vtapp->doShow();
			break;
		case 'doHide':
			$vtapp->evvtSetVisible(0);
			$return=$vtapp->doHide();
			break;
		case 'doSaveAppPosition':
			$wtop = vtlib_purify($_REQUEST['wtop']);
			$wleft = vtlib_purify($_REQUEST['wleft']);
			$wwidth = vtlib_purify($_REQUEST['wwidth']);
			$wheight = vtlib_purify($_REQUEST['wheight']);
			$return=$vtapp->evvtSaveAppPosition($wtop,$wleft,$wwidth,$wheight);
			break;
		case 'doReorderApps':
			$dstcl = vtlib_purify($_REQUEST['dstclass']);
			$dstid = vtlib_purify($_REQUEST['dstappid']);
			if (!empty($dstcl) and !empty($dstid) and is_numeric($dstid))
			$return=doReorderApps($appid,$classname,$dstid,$dstcl);
			break;
		case 'doUninstallApp':
			$vtapp->unInstall();
			include "$mypath/vtapps/app1/vtapp.php";
			$return=vtAppcomTSolucioTrash::unInstallvtApp($appid,$classname);
			break;
		case 'dovtAppMethod':
			$vtappMethod=vtlib_purify($_REQUEST['vtappmethod']);
			$return='';
			if (method_exists($vtapp, $vtappMethod)) 
				$return=$vtapp->$vtappMethod();
			break;
	}
}
echo $return;

function doReorderApps($appid,$classname,$dstid,$dstcl) {
	global $adb,$current_user;
	$dstord=$adb->getone("select sortorder from vtiger_evvtappsuser where appid=$dstid and userid=".$current_user->id);
	$orgord=$adb->getone("select sortorder from vtiger_evvtappsuser where appid=$appid and userid=".$current_user->id);
	$adb->query("update vtiger_evvtappsuser set sortorder=$orgord where appid=$dstid and userid=".$current_user->id);
	$adb->query("update vtiger_evvtappsuser set sortorder=$dstord where appid=$appid and userid=".$current_user->id);
	return '';
}
?>