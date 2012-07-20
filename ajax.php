<?php
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
  case 'VTAPP_getContent':
    $appInstance = $vtAppManager->getAppInstance($appId);
    $data = array(
      'title' => $appInstance->getTitle(),
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