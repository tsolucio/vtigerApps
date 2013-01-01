<?php
/*************************************************************************************************
 * Copyright 2012 JPL TSolucio, S.L. -- This file is a part of evvtApps.
 * You can copy, adapt and distribute the work under the "Attribution-NonCommercial-ShareAlike"
 * Vizsage Public License (the "License"). You may not use this file except in compliance with the
 * License. Roughly speaking, non-commercial users may share and modify this code, but must give credit
 * and share improvements. However, for proper details please read the full License, available at
 * http://vizsage.com/license/Vizsage-License-BY-NC-SA.html and the handy reference for understanding
 * the full license at http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html. Unless required by
 * applicable law or agreed to in writing, any software distributed under the License is distributed
 * on an  "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the
 * License terms of Creative Commons Attribution-NonCommercial-ShareAlike 3.0 (the License).
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