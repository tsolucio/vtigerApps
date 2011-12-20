<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  JPL TSolucio, S.L. Open Source
 * The Initial Developer of the Original Code is JPL TSolucio, S.L.
 * Portions created by JPL TSolucio, S.L. are Copyright (C) JPL TSolucio, S.L.
 * All Rights Reserved.
 ************************************************************************************/

class vtAppcomTSolucioevvtApps extends vtApp {
	
	var $hasedit = true;
	var $hasrefresh = false;
	var $hassize = true;
	var $candelete = false;
	var $wwidth = 250;
	var $wheight = 110;
	var $haseditsize = true;
	var $ewidth = 180;
	var $eheight = 120;

	public function getContent($lang) {		
		return $this->getAbout($lang);
	}

	public function getAbout($lang) {
		$about = '<img src="'.$this->getAppIcon().'" style="float:left" onclick="vtAppChangeIcon('.$this->appid.",'".$this->apppath."/evolutivo.png".'\');"><br/>';
		$about.= '<b>vtEvolutivo::vtApps</b><br/>';
		$about.= 'Copyright &copy; 2012<br/><br/>';
		$about.= date('H:i:s').'<br/>';
		//$about.= '<script language="javascript">alert("jsExec");</script>';		
		return $about;
	}
	public function doResize($lang,$nwidth,$nheight) {		
		return $this->getAbout($lang)."<br>$nwidth"."<br>$nheight";
	}
	public function getEdit($lang) {		
		$editwindow='<br><b>Close me to see my main window refresh!</b><br><br>';
		$editwindow.='<p align=center><input type=button onclick="$(\'#vtappedit'.$this->appid.'\').data(\'kendoWindow\').close()" value="Close"></p>';
		return $editwindow;
	}

}
?>
