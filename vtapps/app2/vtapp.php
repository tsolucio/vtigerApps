<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  JPL TSolucio, S.L. Open Source
 * The Initial Developer of the Original Code is JPL TSolucio, S.L.
 * Portions created by JPL TSolucio, S.L. are Copyright (C) JPL TSolucio, S.L.
 * All Rights Reserved.
 ************************************************************************************/

require_once('Smarty_setup.php');
require_once('include/utils/utils.php');

class vtAppcomTSolucioConfiguration extends vtApp {

	var $hasedit = false;
	var $hasrefresh = true;
	var $hassize = true;
	var $candelete = false;
	var $wwidth = 1100;
	var $wheight = 585;

	public function getContent($lang) {
		global $adb,$current_language,$current_user;
		$smarty = new vtigerCRM_Smarty;
		$smarty->template_dir = $this->apppath;
		if (is_admin($current_user))
			$rsapps=$adb->query('select evvtappsid from vtiger_evvtapps order by evvtappsid');
		else
			$rsapps=$adb->pquery('select evvtappsid from vtiger_evvtapps
			 inner join vtiger_evvtappsuser on appid=evvtappsid
			 where userid=? and wenabled and canwrite order by sortorder',array($current_user->id));
		$apps=array();
		while ($ap=$adb->fetch_array($rsapps)) {
			$appid=$ap['evvtappsid'];
			if ($appid==2) {  // it is this config vtApp
				$apps[]='{ appID: '.$appid.', appName: "'.$this->getAppName($current_language).'", appDescription: "'.$this->getDescription($current_language).'"}';
			} else {
				$loadedclases=get_declared_classes();
				include_once $this->apppath."/../app$appid/vtapp.php";
				$newclass=array_diff(get_declared_classes(), $loadedclases);
				$newclass=array_pop($newclass);
				$newApp=new $newclass($appid);
				$apps[]='{ appID: '.$appid.', appName: "'.$newApp->getAppName($current_language).'", appDescription: "'.$newApp->getDescription($current_language).'"}';
			}
		}
		$apps=implode(',', $apps);
		$smarty->assign('APPS',"[$apps]");
		//$smarty->assign('APPS',"[".$this->getUserAppConfig()."]");
		$smarty->assign('appId',$this->appid);
		$smarty->assign('LBLappID',$this->getvtAppTranslatedString('LBL_appID', $current_language));
		$smarty->assign('appName',$this->getvtAppTranslatedString('LBL_appName', $current_language));
		$smarty->assign('appDescription',$this->getvtAppTranslatedString('LBL_appDesc', $current_language));
		$smarty->assign("User",$this->getvtAppTranslatedString('User', $current_language));
		$smarty->assign("Visible",$this->getvtAppTranslatedString('Visible', $current_language));
		$smarty->assign("Enabled",$this->getvtAppTranslatedString('Enabled', $current_language));
		$smarty->assign("Write",$this->getvtAppTranslatedString('Write', $current_language));
		$smarty->assign("Hide",$this->getvtAppTranslatedString('Hide', $current_language));
		$smarty->assign("Show",$this->getvtAppTranslatedString('Show', $current_language));
		$smarty->assign("Delete",$this->getvtAppTranslatedString('Delete', $current_language));
		return $smarty->fetch('vtappconfig.tpl');
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
