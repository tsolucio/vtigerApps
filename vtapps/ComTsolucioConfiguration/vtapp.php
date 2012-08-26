<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/
require_once('vtlib/thirdparty/dUnzip2.inc.php');

class VtApp_ComTsolucioConfiguration extends vtAppBase {

	// ** ACTIVATE UNINSTALL FEATURE
	var $deleteActive=true;
	// **

	public function getContent() {
	  $vtAppManager = VtAppManager::getInstance();
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
	  if (is_admin($this->getUser())) {
	    $template = 'admin-template.php';
	  } else {
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
		  'description' => $launcher->getShortDescription(),
		  'classname'=> $launcher->getClassName()
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

	public function vtUploadApp($fromCache='') {
		global $adb,$log,$current_language;
		$retrdo = 'NOK';
		if (is_admin($this->getUser())) {
			if (!empty($_FILES) or !empty($fromCache)) {
				if (!empty($fromCache)) {
					$vtAppfile=$fromCache;
					$validFile=true;
				} else {
					$vtAppfile='cache/upload/'.$_FILES['vtupload']["name"];
					$validFile=@move_uploaded_file($_FILES['vtupload']["tmp_name"], $vtAppfile);
				}
				if ($validFile) {
					$validZip=$this->checkZip($vtAppfile);
					if ($validZip) {
						$unzip = new dUnzip2($vtAppfile);
						$unzip->debug=false;
						$getini = $unzip->unzip('vtapp.ini','cache/vtapp.ini');
						if($unzip) $unzip->close();
						if (!empty($getini)) { 
							$data = parse_ini_file('cache/vtapp.ini');
							@unlink('cache/vtapp.ini');
							if (!empty($data['class_name'])) {
								$appid=$adb->getone("select evvtappsid from vtiger_evvtapps where path='".$data['class_name']."'");
								if (empty($appid)) {
									$retmsg=$this->doSetupApp('install',$vtAppfile,$data['class_name']);
								} else {
									$retmsg=$this->doSetupApp('update',$vtAppfile,$data['class_name']);
								}
								$retrdo = 'OK';
							} else {
								$retmsg=$this->translate('invalidvtAppINIFile');
							}
						} else {
							$retmsg=$this->translate('invalidvtAppINIFile');
						}
					} else {
						$retmsg=$this->translate('invalidvtAppFile');
					}
				} else {
					$retmsg=$this->translate('invalidMoveUpload');
				}
			} else {
				$retmsg=$this->translate('invalidUpload');
			}
		} else {
			$retmsg=$this->translate('OnlyAdminUserAllowed');
		}
		return json_encode(array('result'=>$retrdo,'msg'=>$retmsg,'id'=>$this->getId()));
	}

	private function doSetupApp($action,$vtAppfile,$classname) {
		global $log,$adb,$currentModule;
		ob_start();
		if ($action=='install') {
			$adb->query("insert into vtiger_evvtapps (path,installdate) values ('$classname','".date('Y-m-d H:i:s')."')");
			echo "<b>".$this->translate('StartInstall')."</b><br/>";
		} else {
			echo "<b>".$this->translate('StartUpdate')."</b><br/>";
		}
		$targetDir="modules/$currentModule/vtapps/$classname";
		if (!is_dir($targetDir)) mkdir($targetDir);
		$unzip = new dUnzip2($vtAppfile);
		$unzip->debug=false;
		$unzip->unzipAll($targetDir);
		unlink($vtAppfile);
		if($unzip) $unzip->close();
		include_once "$targetDir/vtapp.php";
		$newApp=new $classname();
		if ($action=='install') {
			echo "<b>".$this->translate('Call')." postInstall</b><br/>";
			$newApp->postInstall();
		} else {
			echo "<b>".$this->translate('Call')." postUpdate</b><br/>";
			$newApp->postUpdate();
		}
		$newApp->save();  // new class variables should have been set to their default value in postX()
		$output = ob_get_clean();
		return $output;
	}

	public function unInstallvtApp($appid) {
		global $adb,$currentModule;
		if (is_admin($this->getUser())) {
			if ($appid != $this->getId()) {  // We do not uninstall base vtApp: Configuration
				if ($this->deleteActive) {
					// Save physical path before delete
					$vtapppath = $adb->getone("select path from vtiger_evvtapps where evvtappsid={$appid}");
					$data = parse_ini_file("modules/$currentModule/vtapps/$vtapppath/vtapp.ini");
					include_once("modules/$currentModule/vtapps/$vtapppath/vtapp.php");
					$delapp = new $data['class_name']();
					$delapp->unInstall();  // vtApp uninstall hook
					// delete for all users
					$adb->query("delete from vtiger_evvtappsuser where appid=$appid");
					// delete app data
					$adb->query("delete from vtiger_evvtappsdata where appid=$appid");
					// delete app definition
					$adb->query("delete from vtiger_evvtapps where evvtappsid=$appid");
					// delete from hard disk
					$retrdo=VtApp_ComTsolucioConfiguration::recursive_remove_directory("modules/$currentModule/vtapps/$vtapppath");
					if ($retrdo=='OK') {
						$retmsg=$this->translate('UninstallOK');
					} else {
						$r = explode('::',$retrdo);
						$retmsg=$this->translate($r[0]).' ('.$r[1].')';
						$retrdo='NOK';
					}
				} else {
					$retrdo = 'NOK';
					$retmsg = $this->translate('DeleteDeactivated');
				}
			} else {
				$retrdo = 'NOK';
				$retmsg = $this->translate('NoDelConfig');
			}
		} else {
			$retrdo = 'NOK'; // We do not uninstall base vtApp Configuration
			$retmsg = $this->translate('OnlyAdminUserAllowed');
		}
		return json_encode(array('result'=>$retrdo,'msg'=>$retmsg));
	}

	/**
	 * Check if zipfile is a valid vtApp
	 * @access private
	 */
	private function checkZip($zipfile) {
		$unzip = new dUnzip2($zipfile);
		$unzip->debug=false;
		$filelist = $unzip->getList();
		$vtapp_found = false;
		$vtappini_found = false;
		$languagefile_found = false;
		$vticon_found = false;
		foreach($filelist as $filename=>$fileinfo) {
			$matches = Array();
			preg_match('/vtapp.php/', $filename, $matches);
			if(count($matches)) $vtapp_found = true;
			preg_match('/vtapp.ini/', $filename, $matches);
			if(count($matches)) $vtappini_found = true;
			preg_match('/icon.png/', $filename, $matches);
			if(count($matches)) $vticon_found = true;
			preg_match("/language\/en_us.lang.php/", $filename, $matches);
			if(count($matches)) $languagefile_found = true;
			if ($vtapp_found and $vticon_found and $languagefile_found and $vtappini_found) break;
		}
		$validzip = ($vtapp_found and $vticon_found and $languagefile_found and $vtappini_found);
		if($unzip) $unzip->close();
		return $validzip;
	}

	// ------------ lixlpixel recursive PHP functions -------------
	//
	// recursive_remove_directory( directory to delete, empty )
	// expects path to directory and optional TRUE / FALSE to only empty directories
	//
	// Joe:  Thank you very much lixlpixel
	// I have changed the structure a little and added informative result return value
	// ------------------------------------------------------------
	static public function recursive_remove_directory($directory, $leaveempty=FALSE)
	{
		if(substr($directory,-1) == '/') {
			$directory = substr($directory,0,-1);
		}
		if(!file_exists($directory)) {
			return 'DirNotExists::'.$directory;
		} elseif (!is_dir($directory)) {
			return 'DirNotDir::'.$directory;
		} elseif (!is_readable($directory)) {
			return 'DirNotReadable::'.$directory;
		} else {
			$handle = opendir($directory);
			while (FALSE !== ($item = readdir($handle))) {
				if($item != '.' && $item != '..') {
					$path = $directory.'/'.$item;
					if(is_dir($path)) {
						$subrdo = VtApp_ComTsolucioConfiguration::recursive_remove_directory($path);
						if ($subrdo != 'OK') {
							return $subrdo;
						}
					} else {
						@unlink($path);
					}
				}
			}
			closedir($handle);
			if(!$leaveempty) {
				if(!@rmdir($directory)) {
					return 'DirCannotDel::'.$directory;
				}
			}
		}
		return 'OK';
	}
	// ------------------------------------------------------------

}
?>
