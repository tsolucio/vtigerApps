<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  JPL TSolucio, S.L. Open Source
 * The Initial Developer of the Original Code is JPL TSolucio, S.L.
 * Portions created by JPL TSolucio, S.L. are Copyright (C) JPL TSolucio, S.L.
 * All Rights Reserved.
 ************************************************************************************/

class vtApptldCompanyAppName extends vtApp {
	
	var $hasedit = true;
	var $hasrefresh = true;
	var $hassize = true;
	var $candelete = true;
	var $wwidth = 0;
	var $wheight = 0;
	var $haseditsize = true;
	var $ewidth = 0;
	var $eheight = 0;
	
	public function getContent($lang) {		
		return 'This is the default template widget';
	}

}
?>
