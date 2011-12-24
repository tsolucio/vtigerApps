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
	
	// ** ACTIVATE UNINSTALL FEATURE
	static public $deleteActive=false;
	// **

	var $hasedit = false;
	var $hasrefresh = false;
	var $hassize = false;
	var $candelete = false;
	
	static public function unInstallvtApp($appid,$classname) {
		global $adb,$currentModule;
		$return='OK';
		if (vtAppcomTSolucioTrash::$deleteActive) {
			// delete for all users
			$adb->query("delete from vtiger_evvtappsuser where appid=$appid");
			// delete app definition
			$adb->query("delete from vtiger_evvtapps where evvtappsid=$appid");
			// delete from hard disk
			$rdo=vtAppcomTSolucioTrash::recursive_remove_directory("modules/$currentModule/vtapps/app$appid");
			if (!$rdo) $return='NOK';
		}
		return $return;
	}

	// ------------ lixlpixel recursive PHP functions -------------
	//
	//  Joe:  Thank you very much lixlpixel
	//
	// recursive_remove_directory( directory to delete, empty )
	// expects path to directory and optional TRUE / FALSE to empty
	// ------------------------------------------------------------
	static public function recursive_remove_directory($directory, $empty=FALSE)
	{
		if(substr($directory,-1) == '/')
		{
			$directory = substr($directory,0,-1);
		}
		if(!file_exists($directory) || !is_dir($directory))
		{
			return FALSE;
		}elseif(is_readable($directory))
		{
			$handle = opendir($directory);
			while (FALSE !== ($item = readdir($handle)))
			{
				if($item != '.' && $item != '..')
				{
					$path = $directory.'/'.$item;
					if(is_dir($path)) 
					{
						vtAppcomTSolucioTrash::recursive_remove_directory($path);
					}else{
						unlink($path);
					}
				}
			}
			closedir($handle);
			if($empty == FALSE)
			{
				if(!rmdir($directory))
				{
					return FALSE;
				}
			}
		}
		return TRUE;
	}
	// ------------------------------------------------------------

}
?>
