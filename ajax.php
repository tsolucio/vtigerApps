<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/
require_once('Smarty_setup.php');
require_once('include/utils/utils.php');
require_once('modules/evvtApps/VtAppManager.php');

$operation = $_REQUEST['evvtapps_action'];
$appId = $_REQUEST['evvtapps_appid'];

$vtAppManager = VtAppManager::getInstance();
switch ($operation) {
  case 'VTAPP_getLaunchers':
    $data = $vtAppManager->getUIData();
    echo json_encode($data);
    break;
  case 'VTAPP_createAppInstance':
    $launcherId = $_REQUEST['launcherid'];
    $appInstance = $vtAppManager->createAppInstance($launcherId);
    $data = $appInstance->getUIData();
    echo json_encode($data);
    break;
  case 'VTAPP_removeAppInstance':
    $appId = $_REQUEST['appid'];
    $vtAppManager->removeAppInstance($appId);
    break;
  case 'VTAPP_doReorderApps':
	$neworder = $_REQUEST['vtapp_order'];
	$vtAppManager->doReorderApps($neworder);
	break;
  case 'VTAPP_getDashboardLayout':
  	echo $vtAppManager->getDashboardLayout();
  	break;
  case 'VTAPP_setDashboardLayout':
  	$evvtdblayout = $_REQUEST['evvtdblayout'];
	$vtAppManager->setDashboardLayout($evvtdblayout);
	break;
  case 'VTAPP_getCanvasDefault':
	echo $vtAppManager->getCanvasDefault();
	break;
  case 'VTAPP_setCanvasDefault':
  	$canvas = $_REQUEST['evvtcanvas'];
	$vtAppManager->setCanvasDefault($canvas);
	break;
  case 'VTAPP_getManyTitles':
  	$appid_list = explode(',',vtlib_purify($_REQUEST['appid_list']));
  	$titles = array();
  	foreach ($appid_list as $appId) {
	    $appInstance = $vtAppManager->getAppInstance($appId);
	    $title = $appInstance->getTitle();
	    $titles[$appId] = ($title ? $title : '---'); // this is to avoid sending false as title because it breaks the window buttons
  	}
    echo json_encode($titles);
  	break;
  case 'VTAPP_getContent':
    $appInstance = $vtAppManager->getAppInstance($appId);
    $title = $appInstance->getTitle();
    $data = array(
      'title' => ($title ? $title : '---'), // this is to avoid sending false as title because it breaks the window buttons
      'content' => $appInstance->getContent()
      );
    echo json_encode($data);
    break;
  default:
    $appInstance = $vtAppManager->getAppInstance($appId);
    $params = array();
    $i = 0;
    while (isset($_REQUEST['evvtapps_param_'.$i])) {
      $params[$i] = $_REQUEST['evvtapps_param_'.$i];
      $i++;
    }
    echo call_user_func_array(array($appInstance, $operation), $params);
    break;
}
$vtAppManager->save();

exit;
?>