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
		global $window_width,$adb,$current_user;
		$wwidth=$adb->getone('SELECT wwidth FROM vtiger_evvtappsuser WHERE appid='.$this->appid.' and userid='.$current_user->id);
		if (!empty($wwidth)) $this->wwidth=$wwidth;
		return ($this->wwidth==0 ? $window_width : $this->wwidth);
	}

	public function getHeight()  {
		global $window_height,$adb,$current_user;
		$wheight=$adb->getone('SELECT wheight FROM vtiger_evvtappsuser WHERE appid='.$this->appid.' and userid='.$current_user->id);
		if (!empty($wheight)) $this->wheight=$wheight;
		return ($this->wheight==0 ? $window_height : $this->wheight);
	}

	public function getTop()  {
		global $window_top,$adb,$current_user;
		$wtop=$adb->getone('SELECT wtop FROM vtiger_evvtappsuser WHERE appid='.$this->appid.' and userid='.$current_user->id);
		return (empty($wtop) ? $window_top : $wtop);
	}

	public function getLeft()  {
		global $window_left,$adb,$current_user;
		$wleft=$adb->getone('SELECT wleft FROM vtiger_evvtappsuser WHERE appid='.$this->appid.' and userid='.$current_user->id);
		return (empty($wleft) ? $window_left : $wleft);
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
		$info.='wTop: '.$this->getTop().', ';
		$info.='wLeft: '.$this->getLeft().', ';
		$info.='wWidth: '.$this->getWidth().', ';
		$info.='wHeight: '.$this->getHeight().'}';
		return $info;
	}

	public function doEdit($lang) {		
		return '';
	}
	
	public function doResize($lang,$newWidth=0,$newHeight=0) {		
		return $this->getContent($lang);
	}

	public function doShow() {		
		return '';
	}
	
	public function doHide() {		
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

	public function evvtSetVisible($value) {
		global $adb,$current_user;
		$numrecs=$adb->getone('SELECT count(*) FROM vtiger_evvtappsuser WHERE appid='.$this->appid.' and userid='.$current_user->id);
		if ($numrecs==0) $this->evvtCreateUserApp();
		$adb->pquery("update vtiger_evvtappsuser set wvisible=? where appid=? and userid=?",array($value,$this->appid,$current_user->id));
	}

	public function evvtCreateUserApp() {
		global $adb,$current_user, $window_left, $window_top;
		$rs=$adb->pquery('INSERT INTO vtiger_evvtappsuser
		 (appid,userid,wtop,wleft,wwidth,wheight,wvisible,wenabled,canread,canwrite,candelete,canhide,canshow)
		 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
		 array($this->appid,$current_user->id,$window_top,$window_left,$this->wwidth,$this->wheight,1,1,1,1,$this->candelete,1,1));
	}

	public function evvtSaveAppPosition($wtop,$wleft,$wwidth,$wheight) {
		global $adb,$current_user;
		$numrecs=$adb->getone('SELECT count(*) FROM vtiger_evvtappsuser WHERE appid='.$this->appid.' and userid='.$current_user->id);
		if ($numrecs==0) $this->evvtCreateUserApp();
		$adb->pquery("update vtiger_evvtappsuser set wtop=?,wleft=?,wwidth=?,wheight=? where appid=? and userid=?",
		array($wtop,$wleft,$wwidth,$wheight,$this->appid,$current_user->id));
	}

}
?>
