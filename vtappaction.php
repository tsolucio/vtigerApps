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
	}
}
echo $return;
?>