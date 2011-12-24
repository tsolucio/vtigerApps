<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  JPL TSolucio, S.L. Open Source
 * The Initial Developer of the Original Code is JPL TSolucio, S.L.
 * Portions created by JPL TSolucio, S.L. are Copyright (C) JPL TSolucio, S.L.
 * All Rights Reserved.
 ************************************************************************************/

class vtAppcomTSolucioTrash extends vtApp {
	
	var $hasedit = false;
	var $hasrefresh = false;
	var $hassize = false;
	var $candelete = false;
	var $wwidth = 100;
	var $wheight = 100;

	static public function unInstallvtApp($appid,$classname) {
		global $adb;
		// delete for all users
		// $adb->query("delete from vtiger_evvtappsuser where appid=$appid");
		// delete app definition
		// $adb->query("delete from vtiger_evvtapps where evvtappsid=$appid");
		// delete from hard disk
		// rm -rf vtapp{$appid}
		return 'OK'; // error';
	}
}
?>
