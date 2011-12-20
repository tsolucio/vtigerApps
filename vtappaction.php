<?php
$action=vtlib_purify($_REQUEST['vtappaction']);
$classname=vtlib_purify($_REQUEST['class']);
$appid=vtlib_purify($_REQUEST['appid']);
$return='';
if (!empty($classname) and !empty($action) and !empty($appid)) {
	global $current_language;
	$mypath="modules/$currentModule";
	include_once "$mypath/processConfig.php";
	include_once "$mypath/vtapps/baseapp/vtapp.php";
	include "$mypath/vtapps/app$appid/vtapp.php";
	$vtapp=new $classname($appid);
	switch ($action) {
		case 'getAbout':
			$return=$vtapp->getAbout($current_language);
			break;
		case 'getContent':
			$return=$vtapp->getContent($current_language);
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
	}
}
echo $return;
?>