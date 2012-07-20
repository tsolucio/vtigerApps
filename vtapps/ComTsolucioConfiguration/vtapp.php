<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  JPL TSolucio, S.L. Open Source
 * The Initial Developer of the Original Code is JPL TSolucio, S.L.
 * Portions created by JPL TSolucio, S.L. are Copyright (C) JPL TSolucio, S.L.
 * All Rights Reserved.
 ************************************************************************************/

class VtApp_ComTsolucioConfiguration extends vtAppBase {

	public function getContent() {
	  $vtAppManager = VtAppManager::getInstance();
	  if (is_admin($this->getUser())) {
	    $launchers = $vtAppManager->getLaunchers();
	    $appData = array();
	    foreach($launchers as $launcher) {
	      $apps[] = array(
	        'appID' => $launcher->getId(),
	        'appName' => $launcher->getName(),
	        'appDescription' => $launcher->getShortDescription()
	        );
	    }
	    $jsonAppData = json_encode($appData);
	    $template = 'admin-template.php';
	  }
	  else {
	    $template = 'user-template.php';
	  }
	  ob_start();
	  require($this->getPath($template));
	  $output = ob_get_clean();
		return $output;
	}
	
	public function getAppUserData($appId) {
	  $vtAppManager = VtAppManager::getInstance();
		$launcher = $vtAppManager->getLauncher($appId);
		$data = array(
		  'icon' => $launcher->getIconPath(),
		  'description' => $launcher->getShortDescription()
		  );
		
		
		
		return json_encode($data);
	}

	public function getUserAppConfig() {
		global $adb,$current_user,$log;
		$ret=array();
		$fetch_appid=vtlib_purify($_REQUEST['userappid']);
		if (empty($fetch_appid)) {
			$ret['results'][]='{}';
			$ret['total'][]=0;
		} else {
			$private=(!is_admin($current_user) ? 'private' : '');
			$usrs=get_user_array(false, 'Active', '',$private);
			foreach ($usrs as $uid=>$uname) {
				$rsapps=$adb->query("select * from vtiger_evvtappsuser where appid=$fetch_appid and userid=$uid");
				if ($adb->num_rows($rsapps)==0) {  // No record for this app and user > we create it
					if ($fetch_appid==$this->appid) {
						$this->evvtCreateUserApp($uid);
					} else {
						$loadedclases=get_declared_classes();
						include_once $this->apppath."/../app$fetch_appid/vtapp.php";
						$newclass=array_diff(get_declared_classes(), $loadedclases);
						$newclass=array_pop($newclass);
						$newApp=new $newclass($fetch_appid);
						$newApp->evvtCreateUserApp($uid);
					}
					$rsapps=$adb->query("select * from vtiger_evvtappsuser where appid=$fetch_appid and userid=$uid");
				}
				$row=$adb->fetch_array($rsapps);
				$rec=array('evvtappsuserid'=>$row['evvtappsuserid'],
				  'appUser'=>getUserFullName($uid),
                  'appVisible'=>$row['wvisible'],
                  'appEnabled'=>$row['wenabled'],
                  'appWrite'=>$row['canwrite'],
                  'appHide'=>$row['canhide'],
                  'appShow'=>$row['canshow'],
                  'appDelete'=>$row['candelete']);
				$ret['results'][]=$rec;
			}
			$ret['total'][]=count($usrs);
		}
		return json_encode($ret);
	}

	public function setUserAppConfig() {
		global $adb,$log;
		$evvtappsuser=vtlib_purify($_REQUEST['evvtappsuser']);
		if (!empty($evvtappsuser)) {
			$ev=json_decode($evvtappsuser,true);
			$rs=$adb->pquery('UPDATE vtiger_evvtappsuser set
			 wvisible=?,wenabled=?,canwrite=?,candelete=?,canhide=?,canshow=?
			 where evvtappsuserid=?',
		 	array($ev['appVisible'],$ev['appEnabled'],$ev['appWrite'],$ev['appDelete'],$ev['appHide'],$ev['appShow'],$ev['evvtappsuserid']));
		}
	}
	
}
?>
