<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/

class VtApp_ComTsolucioConfiguration extends vtAppBase {

	public function getContent() {
	  $vtAppManager = VtAppManager::getInstance();
	  //if (is_admin($this->getUser())) {
	    $launchers = $vtAppManager->getAllLaunchers();
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
	  //} else {
	  //  $template = 'user-template.php';
	  //}
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

	public function getUserAppConfig($fetch_appid) {
		global $adb,$current_user,$log;
		$ret=array();
		if (empty($fetch_appid)) {
			$ret['results'][]='{}';
			$ret['total'][]=0;
		} else {
			$private=(!is_admin($current_user) ? 'private' : '');
			$usrs=get_user_array(false, 'Active', '',$private);
			foreach ($usrs as $uid=>$uname) {
				$rsapps=$adb->query("select * from vtiger_evvtappsuser where appid=$fetch_appid and userid=$uid");
				if ($adb->num_rows($rsapps)==0) {  // No record for this app and user > we create it
					$this->evvtCreateUserApp($uid,$fetch_appid);
					$rsapps=$adb->query("select * from vtiger_evvtappsuser where appid=$fetch_appid and userid=$uid");
				}
				$row=$adb->fetch_array($rsapps);
				$rec=array('evvtappsuserid'=>$row['evvtappsuserid'],
				  'appUser'=>getUserFullName($uid),
                  'appVisible'=>($row['wvisible'] ? true : false),
                  'appEnabled'=>($row['wenabled'] ? true : false),
                  'appWrite'=>($row['canwrite'] ? true : false),
                  'appHide'=>($row['canhide'] ? true : false),
                  'appShow'=>($row['canshow'] ? true : false));
				$ret['results'][]=$rec;
			}
			$ret['total'][]=count($usrs);
		}
		return json_encode($ret);
	}

	public function setUserAppConfig($params) {
		global $adb,$log;
		$evvtappsuser=$_REQUEST['evvtapps_param_1'];
		if (!empty($evvtappsuser['data'])) {
			$ev=$evvtappsuser['data'];
			$rs=$adb->pquery('UPDATE vtiger_evvtappsuser set
			 wvisible=?,wenabled=?,canwrite=?,canhide=?,canshow=?
			 where evvtappsuserid=?',
		 	array(($ev['appVisible']=='false' ? 0 : 1),
		 		($ev['appEnabled']=='false' ? 0 : 1),
		 		($ev['appWrite']=='false' ? 0 : 1),
				($ev['appHide']=='false' ? 0 : 1),
				($ev['appShow']=='false' ? 0 : 1),
				$ev['evvtappsuserid']));
		}
	}

	// Create configuration record for a user
	protected function evvtCreateUserApp($userid=0,$appid=0) {
		global $adb,$current_user;
		if (empty($userid)) $userid=$current_user->id;
		if (empty($appid)) $appid=$this->getId();
		$rs=$adb->pquery('INSERT INTO vtiger_evvtappsuser
				(appid,userid,wvisible,wenabled,canwrite,canhide,canshow)
				VALUES (?,?,1,1,1,1,1)', array($appid,$userid));
	}
}
?>
