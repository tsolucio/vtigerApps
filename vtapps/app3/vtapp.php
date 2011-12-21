<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  JPL TSolucio, S.L. Open Source
 * The Initial Developer of the Original Code is JPL TSolucio, S.L.
 * Portions created by JPL TSolucio, S.L. are Copyright (C) JPL TSolucio, S.L.
 * All Rights Reserved.
 ************************************************************************************/

class vtAppcomTSolucioAppStore extends vtApp {
	
	var $hasedit = false;
	var $hasrefresh = false;
	var $hassize = true;
	var $candelete = false;
	var $wwidth = 250;
	var $wheight = 110;

	public function getContent($lang) {
		$output = "<br><b>This vtApp will permit the user to connect to the vtApp market place, search for vtApps, buy/pay them and directly download and install them.</b>";		
		return $output;
	}

}
?>
