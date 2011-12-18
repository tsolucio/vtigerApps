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
			// FIXME : Save app status for this user
			$return=$vtapp->doShow();
			break;
		case 'doHide':
			// FIXME : Save app status for this user
			$return=$vtapp->doHide();
			break;
	}
}
echo $return;
?>