<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  JPL TSolucio, S.L. Open Source
 * The Initial Developer of the Original Code is JPL TSolucio, S.L.
 * Portions created by JPL TSolucio, S.L. are Copyright (C) JPL TSolucio, S.L.
 * All Rights Reserved.
 ************************************************************************************/
require_once('vtlib/thirdparty/dUnzip2.inc.php');

class VtApp_ComTsolucioAppStore extends vtAppBase {
	
	var $hasedit = false;
	var $hasrefresh = false;
	var $hassize = true;
	var $candelete = false;
	var $wwidth = 250;
	var $wheight = 110;

	public function getContent() {
		$output = "<br><b>This vtApp will permit the user to connect to the vtApp market place, search for vtApps, buy/pay them and directly download and install them.</b>";
		$output.= '<br><form method="post" style="width:60%">
                <div>
                	<br>Upload a vtApp to have it installed:<br>
                    <input name="vtinstall" id="vtinstall" type="file" />
                </div>
                <div>
                	<br>Upload a vtApp to have it updated:<br>
                    <input name="vtupdate" id="vtupdate" type="file" />
                </div>
            </form>';
		return $output;
	}

	public function vtInstallApp($fileFromCache='') {
		global $log,$current_language;
		$validApp='';
		if (!empty($_FILES) or !empty($fromCache)) {
			if (!empty($fromCache)) {
				$vtAppfile=$fromCache;
				$validFile=true;
			} else {
				$vtAppfile='cache/upload/'.$_FILES['vtinstall']["name"];
				$validFile=@move_uploaded_file($_FILES['vtinstall']["tmp_name"], $vtAppfile);
			}
			if ($validFile) {
				$validZip=$this->checkZip($vtAppfile);
				if ($validZip) {
					$this->doInstallApp($vtAppfile);
				} else {
					$validApp=$this->getvtAppTranslatedString('invalidvtAppFile', $current_language);
				}
			} else {
				$validApp=$this->getvtAppTranslatedString('invalidMoveUpload', $current_language);
			}
		} else {
			$validApp=$this->getvtAppTranslatedString('invalidUpload', $current_language);
		}
		return $validApp;
	}

	public function vtUpdateApp($fileFromCache='') {
		global $log,$adb,$current_language;
		$validApp='';
		if (!empty($_FILES) or !empty($fromCache)) {
			if (!empty($fromCache)) {
				$vtAppfile=$fromCache;
				$validFile=true;
			} else {
				$vtAppfile='cache/upload/'.$_FILES['vtupdate']["name"];
				$validFile=@move_uploaded_file($_FILES['vtupdate']["tmp_name"], $vtAppfile);
			}
			if ($validFile) {
				$classname=basename($vtAppfile,'.zip');
				$appid=$adb->getone("select evvtappsid from vtiger_evvtapps where LOWER(appname)='".strtolower($classname)."'");
				if (!empty($appid)) {
					$this->doUpdateApp($vtAppfile,$appid);
				} else {
					$validApp=$this->getvtAppTranslatedString('invalidvtAppFile', $current_language);
				}
			} else {
				$validApp=$this->getvtAppTranslatedString('invalidMoveUpload', $current_language);
			}
		} else {
			$validApp=$this->getvtAppTranslatedString('invalidUpload', $current_language);
		}
		return $validApp;
	}

	/**
	 * Check if zipfile is a valid vtApp
	 * @access private
	 */
	function checkZip($zipfile) {
		$unzip = new dUnzip2($zipfile);
		$unzip->debug=false;
		$filelist = $unzip->getList();
		$vtapp_found = false;
		$languagefile_found = false;
		$vticon_found = false;
		foreach($filelist as $filename=>$fileinfo) {
			$matches = Array();
			preg_match('/vtapp.php/', $filename, $matches);
			if(count($matches)) $vtapp_found = true;
			preg_match('/icon.png/', $filename, $matches);
			if(count($matches)) $vticon_found = true;
			preg_match("/language\/en_us.lang.php/", $filename, $matches);
			if(count($matches)) $languagefile_found = true;
			if ($vtapp_found and $vticon_found and $languagefile_found) break;
		}
		$validzip = ($vtapp_found and $vticon_found and $languagefile_found); 
		if($unzip) $unzip->close();
		return $validzip;
	}

	private function doInstallApp($vtAppfile) {
		global $log,$adb,$current_language,$currentModule;
		$adb->query("insert into vtiger_evvtapps (appname,installdate) values ('installing','".date('Y-m-d H:i:s')."')");
		$newappid=$adb->getLastInsertID();
		$targetDir="modules/$currentModule/vtapps/app$newappid";
		if (!is_dir($targetDir)) mkdir($targetDir);
		$unzip = new dUnzip2($vtAppfile);
		$unzip->debug=false;
		$unzip->unzipAll($targetDir);
		unlink($vtAppfile);
		$loadedclases=get_declared_classes();
		include_once "$targetDir/vtapp.php";
		$newclass=array_diff(get_declared_classes(), $loadedclases);
		$newclass=array_pop($newclass);
		$adb->pquery('update vtiger_evvtapps set appname=? where evvtappsid=?',array($newclass,$newappid));
		$newApp=new $newclass($newappid);
		$rsusr=$adb->query('select id from vtiger_users');
		while ($usr=$adb->fetch_array($rsusr)) {
			$newApp->evvtCreateUserApp($usr['id']);
		}
		$newApp->postInstall();
		if($unzip) $unzip->close();
	}
	private function doUpdateApp($vtAppfile,$newappid) {
		global $log,$adb,$current_language,$currentModule;
		$targetDir="modules/$currentModule/vtapps/app$newappid";
		$unzip = new dUnzip2($vtAppfile);
		$unzip->debug=false;
		$unzip->unzipAll($targetDir);
		unlink($vtAppfile);
		$loadedclases=get_declared_classes();
		include_once "$targetDir/vtapp.php";
		$newclass=array_diff(get_declared_classes(), $loadedclases);
		$newclass=array_pop($newclass);
		$newApp=new $newclass($newappid);
		$newApp->postUpdate();
		if($unzip) $unzip->close();
	}
}
?>
