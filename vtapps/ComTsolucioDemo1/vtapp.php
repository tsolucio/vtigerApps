<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  JPL TSolucio, S.L. Open Source
 * The Initial Developer of the Original Code is JPL TSolucio, S.L.
 * Portions created by JPL TSolucio, S.L. are Copyright (C) JPL TSolucio, S.L.
 * All Rights Reserved.
 ************************************************************************************/

class VtApp_ComTsolucioDemo1 extends vtAppBase {
	
	public function getContent() {
	  $about = "<div id=\"content\">";
		$about.= '<img id="image-button" src="'.$this->getLauncher()->getIconPath().'" style="float:left"><br/>';
		$about.= '<b>vtEvolutivo::vtApps</b><br/>';
		$about.= 'Copyright &copy; 2012<br/><br/>';
		$about.= 'Click on the icon to see my Canvas Icon change<br/><br/>';
		$about.= date('H:i:s').'<br/>';
		$about.= '<div id="content-resize"></div>';
		$about.= "</div>";
		//$about.= '<script language="javascript">alert("jsExec");</script>';
		return $about;
	}
}
?>