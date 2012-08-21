<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/

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