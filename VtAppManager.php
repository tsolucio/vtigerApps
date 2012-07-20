<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  JPL TSolucio, S.L. Open Source
 * The Initial Developer of the Original Code is JPL TSolucio, S.L.
 * Portions created by JPL TSolucio, S.L. are Copyright (C) JPL TSolucio, S.L.
 * All Rights Reserved.
 ************************************************************************************/

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
	
	// Get all launchers
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
	  $data = unserialize($row['data']);
	  $appInstance->setProperties($data);
	}
	
	// Save app state to persistent storage
	protected function saveAppInstance($appInstance) {
	  global $adb;
	  $data = $appInstance->getProperties();
	  $dataSerialized = $adb->sql_escape_string(serialize($data));
	  $query = "update vtiger_evvtappsdata set top='{$appInstance->getTop()}', `left`='{$appInstance->getLeft()}', width='{$appInstance->getWidth()}', height='{$appInstance->getHeight()}', data='{$dataSerialized}' where id={$appInstance->getId()}";
	  $adb->query($query);
	}
}
?>