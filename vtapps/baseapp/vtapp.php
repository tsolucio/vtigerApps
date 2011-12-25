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
	var $apppath;
	var $hasedit = true;
	var $hasrefresh = true;
	var $hassize = true;
	var $candelete = true;
	var $wwidth = 0;
	var $wheight = 0;
	var $haseditsize = true;
	var $ewidth = 0;
	var $eheight = 0;
	
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
		global $adb,$current_user;
		$canwrite=$adb->getone('SELECT canwrite FROM vtiger_evvtappsuser WHERE appid='.$this->appid.' and userid='.$current_user->id);
		$canwrite=(is_null($canwrite) ? ($this->hasedit and $this->getEdit('en_us')!='') : ($canwrite and $this->getEdit('en_us')!='')); // at least we have screen in english
		return $canwrite;
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
		global $adb,$current_user;
		$candelete=$adb->getone('SELECT candelete FROM vtiger_evvtappsuser WHERE appid='.$this->appid.' and userid='.$current_user->id);
		return (is_null($candelete) ? $this->candelete : $candelete);
	}

	public function canClose()  {
		global $adb,$current_user;
		$canhide=$adb->getone('SELECT canhide FROM vtiger_evvtappsuser WHERE appid='.$this->appid.' and userid='.$current_user->id);
		return (is_null($canhide) ? true : $canhide);  // by default all windows can be closed
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
		return (is_null($wtop) ? $window_top : $wtop);
	}

	public function getLeft()  {
		global $window_left,$adb,$current_user;
		$wleft=$adb->getone('SELECT wleft FROM vtiger_evvtappsuser WHERE appid='.$this->appid.' and userid='.$current_user->id);
		return (is_null($wleft) ? $window_left : $wleft);
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

	public function getEditWidth()  {
		global $edit_window_width;
		return (empty($this->ewidth) ? $edit_window_width : $this->ewidth);
	}

	public function getEditHeight()  {
		global $edit_window_height;
		return (empty($this->eheight) ? $edit_window_height : $this->eheight);
	}

	public function getContent($lang) {		
		return 'This is the default empty widget';
	}

	public function getCanvasJavascript($lang) {		
		return '';
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
		$info.='hasEdit: '.($this->getHasEdit() ? '1' : '0').', ';
		$info.='hasRefresh: '.($this->hasrefresh ? '1' : '0').', ';
		$info.='hasSize: '.($this->hassize ? '1' : '0').', '; 
		$info.='canDelete: '.($this->canDelete() ? '1' : '0').', ';
		$info.='canClose: '.($this->canClose() ? '1' : '0').', ';
		$info.='wTop: '.$this->getTop().', ';
		$info.='wLeft: '.$this->getLeft().', ';
		$info.='wWidth: '.$this->getWidth().', ';
		$info.='wHeight: '.$this->getHeight().'}';
		return $info;
	}

	public function getEditInfo($lang)  {
		if ($this->getHasEdit()) {
		$info ='{title: "'.$this->getvtAppTranslatedString('Edit',$lang).' '.$this->getvtAppTranslatedString('appName',$lang).'", ';
		$info.='className: "'.get_class($this).'", ';
		$info.='hasSize: '.($this->haseditsize ? '1' : '0').', '; 
		$info.='wWidth: '.$this->getEditWidth().', ';
		$info.='wHeight: '.$this->getEditHeight().'}';
		} else {
		$info = '{}';
		}
		return $info;
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

	public function postUpdate() {		
		return '';
	}

	public function postInstall() {		
		return '';
	}

	public function unInstall() {		
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

	public function evvtCreateUserApp($userid=0) {
		global $adb,$current_user, $window_left, $window_top;
		if (empty($userid)) $userid=$current_user->id;
		$rs=$adb->pquery('INSERT INTO vtiger_evvtappsuser
		 (appid,userid,wtop,wleft,wwidth,wheight,wvisible,wenabled,canwrite,candelete,canhide,canshow)
		 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
		 array($this->appid,$userid,$window_top,$window_left,$this->wwidth,$this->wheight,1,1,1,$this->candelete,1,1));
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
