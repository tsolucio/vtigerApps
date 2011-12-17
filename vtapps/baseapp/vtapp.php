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
	var $wwidth = 0;
	var $wheight = 0;
	
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

	public function getWidth()  {
		global $window_width;
		return ($this->wwidth==0 ? $window_width : $this->wwidth);
	}

	public function getHeight()  {
		global $window_height;
		return ($this->wheight==0 ? $window_height : $this->wheight);
	}

	public function getTitle($lang) {
		return $this->getvtAppTranslatedString('Title',$lang);
	}
	
	public function getTooltipDescription($lang) {
		return $this->getvtAppTranslatedString('TooltipDescription',$lang);
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

	public function getAppInfo($lang)  {
		$info ='{appName: "'.$this->getvtAppTranslatedString('appName',$lang).'", ';
		$info.='appTitle: "'.$this->getvtAppTranslatedString('Title',$lang).'", ';
		$info.='className: "'.get_class($this).'", ';
		$info.='hasEdit: '.(($this->hasedit and $this->getEdit('en_us')!='') ? '1' : '0').', ';
		$info.='hasRefresh: '.($this->hasrefresh ? '1' : '0').', ';
		$info.='hasSize: '.($this->hassize ? '1' : '0').', '; 
		$info.='canDelete: '.($this->candelete ? '1' : '0').', ';
		$info.='wWidth: '.$this->getWidth().', ';
		$info.='wHeight: '.$this->getHeight().'}';
		return $info;
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
		global $log;
		// OPTIMIZE: use cache to save translation array and not load from disk each time
		$trstr=$key;
		if (file_exists($this->apppath."/language/$lang.lang.php")) {
			include $this->apppath."/language/$lang.lang.php";
			if (!empty($vtapps_strings[$key])) $trstr=$vtapps_strings[$key];
		} else if (file_exists($this->apppath."/language/en_us.lang.php")) {
			include $this->apppath."/language/en_us.lang.php";
			if (!empty($vtapps_strings[$key])) $trstr=$vtapps_strings[$key];
		}
		return $trstr;
	}

}
?>
