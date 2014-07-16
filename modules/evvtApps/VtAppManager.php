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

require_once('modules/evvtApps/VtAppLauncher.php');
require_once('modules/evvtApps/VtAppBase.php');

/**
 * This object manages app launchers and instances
 */
class VtAppManager {
  
  protected static $instance = NULL;
  
  protected $user;
  protected $language;
  protected $vtappsPath;
  protected $launchersPool;
  protected $appInstancesPool;
  
  // Get singleton instance for this object
  public static function getInstance() {
    if (is_null(self::$instance)) {
      self::$instance = new VtAppManager();
    }
    return self::$instance;
  }
  
  protected function __construct() {
    global $current_language, $current_user, $currentModule;
    $this->user = $current_user;
    $this->language = $current_language;
    $this->vtappsPath = "modules/{$currentModule}/vtapps";
    $this->launchersPool = array();
    $this->appInstancesPool = array();
  }
  
  // Get user object
  public function getUser() {
    return $this->user;
  }
  
  // Get current user id
  public function getUserId() {
    return $this->user->id;
  }
  
  // Get user language
  public function getLanguage() {
    return $this->language;
  }
  
	// Get app launcher by id
	public function getLauncher($id) {
	  global $adb;
	  if (!array_key_exists($id, $this->launchersPool)) {
	    $query = "select path from vtiger_evvtapps where evvtappsid={$id}";
	    $res = $adb->query($query);
	    $launcherPath = 'modules/evvtApps/vtapps/'.$adb->query_result($res, 0, 0);
	    $this->launchersPool[$id] = new VtAppLauncher($id, $launcherPath);
	  }
	  return $this->launchersPool[$id];
	}
	
	// Get enabled launchers
  public function getLaunchers() {
	  global $adb;
	  $launchers = array();
	  $query = "select evvtappsid from vtiger_evvtapps join vtiger_evvtappsuser on appid=evvtappsid where userid={$this->getUserId()} and wenabled order by sortorder";
	  $res = $adb->query($query);
	  while ($row=$adb->getNextRow($res, false)) {
	    $launchers[] = $this->getLauncher($row['evvtappsid']);
	  }
	  return $launchers;
	}

	// Get all launchers
	public function getAllLaunchers() {
		global $adb;
		$launchers = array();
		$query = "select evvtappsid from vtiger_evvtapps order by path";
		$res = $adb->query($query);
		while ($row=$adb->getNextRow($res, false)) {
			$launchers[] = $this->getLauncher($row['evvtappsid']);
		}
		return $launchers;
	}

	// Create app instance
  public function createAppInstance($launcherId) {
    global $adb;
    $launcher = $this->getLauncher($launcherId);
    // Create app instance in database
    $query = "insert into vtiger_evvtappsdata (appid, userid) values ({$launcher->getId()}, {$this->getUserId()})";
    $adb->query($query);
    $appInstanceId = $adb->getLastInsertID();
    // Create object and save it in the pool
    $className = $launcher->getClassName();
    $appInstance = new $className($launcher, $appInstanceId);
    $appInstance->resizeWindow($launcher->getWindowDefaultWidth(), $launcher->getWindowDefaultHeight());
    $this->saveAppInstance($appInstance);
    $this->appInstancesPool[$appInstanceId] = $appInstance;
    return $appInstance;
  }
  
  // Get app instance for this launcher
  public function getAppInstance($appInstanceId) {
    global $adb;
    if (!array_key_exists($appInstanceId, $this->appInstancesPool)) {
      $query = "select appid from vtiger_evvtappsdata where id={$appInstanceId}";
      $res = $adb->query($query);
      $launcherId = $adb->query_result($res, 0, 0);
      $launcher = $this->getLauncher($launcherId);
      $className = $launcher->getClassName();
      $appInstance = new $className($launcher, $appInstanceId);
      $this->loadAppInstance($appInstance);
      $this->appInstancesPool[$appInstanceId] = $appInstance;
    }
    return $this->appInstancesPool[$appInstanceId];
  }
	
  // Remove app instance
  public function removeAppInstance($appInstanceId) {
    global $adb;
    $query = "delete from vtiger_evvtappsdata where id={$appInstanceId}";
    $adb->query($query);
  }
  
  // Get UI data for all instances of this launcher's app
  public function getUIData() {
    $launchers = $this->getLaunchers();
    $data = array();
    foreach($launchers as $launcher) {
      $launcherId = $launcher->getId();
      $launcherData = $launcher->getUIData();
      $launcherData['appInstances'] = array();
      $appInstances = $this->getAppInstancesByLauncher($launcherId);
      if (empty($appInstances) && (!$launcher->isVisible() || !$launcher->isClonable())) {
        $appInstances = array($this->createAppInstance($launcherId));
      }
      foreach($appInstances as $appInstance) {
        $launcherData['appInstances'][] = $appInstance->getUIData();
      }
      $data[] = $launcherData;
    }
    return $data;
  }
  
  // Get all app instances for this launcher
  public function getAppInstancesbyLauncher($launcherId) {
    global $adb;
    $appInstances = array();
    $userId = $this->getUserId();
    $query = "select id from vtiger_evvtappsdata where appid={$launcherId} and userid={$userId}";
    $res = $adb->query($query);
    while ($row=$adb->getNextRow($res, false)) {
      $appInstances[] = $this->getAppInstance($row['id']);
    }
    return $appInstances;
  }
  
  // Save everything
  public function save() {
    foreach($this->appInstancesPool as $appInstance) {
      $this->saveAppInstance($appInstance);
    }
  }
  
  // Load app state from persistent storage
	protected function loadAppInstance(&$appInstance) {
	  global $adb;
	  $query = "select * from vtiger_evvtappsdata where id={$appInstance->getId()}";
	  $res = $adb->query($query);
	  $row = $adb->getNextRow($res, false);
	  $appInstance->moveWindow($row['top'], $row['left']);
	  $appInstance->resizeWindow($row['width'], $row['height']);
	  $appInstance->windowOnScreen($row['onscreen']);
	  $data = unserialize($row['data']);
	  $appInstance->setProperties($data);
	}
	
	// Save app state to persistent storage
	public function saveAppInstance($appInstance) {
	  global $adb;
	  $data = $appInstance->getProperties();
	  $dataSerialized = $adb->sql_escape_string(serialize($data));
	  $query = "update vtiger_evvtappsdata set top='{$appInstance->getTop()}', `left`='{$appInstance->getLeft()}', width='{$appInstance->getWidth()}', height='{$appInstance->getHeight()}', onscreen='{$appInstance->getOnScreen()}', data='{$dataSerialized}' where id={$appInstance->getId()}";
	  $adb->query($query);
	}

	public function doReorderApps($neworder) {
		global $adb,$current_user,$log;
		if (empty($neworder) or !is_array($neworder)) return false;
		$order = 1;
		foreach ($neworder as $vtapp) {
			$vtappid=str_replace('vtapp-launcher-','',$vtapp);
			$updq="update vtiger_evvtappsuser set sortorder=$order where appid=$vtappid and userid=".$current_user->id;
			$adb->query($updq);
			$order++;
		}
		return true;
	}

	public function getDashboardLayout() {
		global $current_user,$adb;
		$ret = '{}';
		$query = "select dashboarddata from vtiger_evvtappscanvas where userid={$current_user->id}";
		$res = $adb->query($query);
		if ($adb->num_rows($res)>0) {
		  $ret = $adb->query_result($res, 0, 0);
		}
		return $ret;
	}

	public function setDashboardLayout($dblayout) {
		global $current_user,$adb,$log;
		$query = "select count(*) from vtiger_evvtappscanvas where userid={$current_user->id}";
		$ret = $adb->getone($query);
		if ($ret==0) {
			$query = "insert into vtiger_evvtappscanvas (defaultcanvas,windowsdata,dashboarddata,allappsdata,userid) values ('windows','',?,'',?)";
		} else {
			$query = "update vtiger_evvtappscanvas set dashboarddata = ? where userid=?";
		}
		$adb->pquery($query,array($dblayout,$current_user->id));
	}

	public function getCanvasDefault() {
		global $current_user,$adb;
		$ret = 'windows';
		$query = "select defaultcanvas from vtiger_evvtappscanvas where userid={$current_user->id}";
		$res = $adb->query($query);
		if ($adb->num_rows($res)>0) {
		  $ret = $adb->query_result($res, 0, 0);
		}
		return $ret;
	}
	
	public function setCanvasDefault($canvas) {
		global $current_user,$adb;
		$query = "select count(*) from vtiger_evvtappscanvas where userid={$current_user->id}";
		$ret = $adb->getone($query);
		if ($ret==0) {
			$query = "insert into vtiger_evvtappscanvas (defaultcanvas,windowsdata,dashboarddata,allappsdata,userid) values (?,'','','',?)";
		} else {
			$query = "update vtiger_evvtappscanvas set defaultcanvas = ? where userid=?";
		}
		$adb->pquery($query,array($canvas,$current_user->id));
	}

}
?>