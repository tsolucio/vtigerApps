<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  JPL TSolucio, S.L. Open Source
 * The Initial Developer of the Original Code is JPL TSolucio, S.L.
 * Portions created by JPL TSolucio, S.L. are Copyright (C) JPL TSolucio, S.L.
 * All Rights Reserved.
 ************************************************************************************/

class vtApp {
	
	var $appid;
	var $hasedit = true;
	var $hasrefresh = true;
	var $hassize = true;
	var $candelete = true;
	
	function __construct($myId) {
		global $currentModule;
		$this->appid=$myId;
		$this->apppath="modules/$currentModule/vtapps/app$myId";		
	}

	public function getAppName($lang)  {
		return $this->getvtAppTranslatedString('appName',$lang);
	}

	public function getAppIcon()  {
		return $this->apppath."/icon.png";
	}

	public function setHasEdit($value) {
		$this->hasedit = $value;
	}
	
	public function getHasEdit()  {
		return $this->hasedit and $this->getEdit('en_us')!=''; // at least we have screen in english
	}
	
	public function setHasRefresh($value) {
		$this->hasrefresh = $value;
	}
	
	public function getHasRefresh()  {
		return $this->hasrefresh;
	}
	
	public function setHasSize($value) {
		$this->hassize = $value;
	}
	
	public function getHasSize()  {
		return $this->hassize;
	}

	public function canDelete()  {
		return $this->candelete;
	}

	public function getTitle($lang) {
		return $this->getvtAppTranslatedString('Title',$lang);
	}
	
	public function getTooltipDescription($lang) {
		return $this->getvtAppTranslatedString('TooltipDescipriton',$lang);
	}

	public function getEdit($lang) {		
		return '';
	}
	
	public function getContent($lang) {		
		return 'This is the default empty widget';
	}

	public function getDescription($lang) {		
		return '';
	}

	public function getAbout($lang) {		
		return '';
	}

	public function doEdit($lang) {		
		return '';
	}
	
	public function doRefresh($lang) {		
		return '';
	}

	public function doShow($lang) {		
		return '';
	}
	
	public function doHide($lang) {		
		return '';
	}

	public function preInstall() {		
		return '';
	}

	public function postInstall() {		
		return '';
	}

	public function getvtAppTranslatedString($key,$lang) {
		global $currentModule;
		$trstr=$key;
		if (file_exists($this->apppath."/language/$lang.lang.php")) {
			include_once $this->apppath."/language/$lang.lang.php";
			if (!empty($vtapps_strings[$key])) $trstr=$vtapps_strings[$key];
		} else if (file_exists($this->apppath."/language/en_us.lang.php")) {
			include_once $this->apppath."/language/en_us.lang.php";
			if (!empty($vtapps_strings[$key])) $trstr=$vtapps_strings[$key];
		}
		return $trstr;
	}

}
?>
