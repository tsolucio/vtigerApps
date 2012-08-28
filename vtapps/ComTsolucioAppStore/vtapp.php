<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/

class VtApp_ComTsolucioAppStore extends vtAppBase {
	
	public function getContent() {
		$output = "<br><b>This vtApp will permit the user to connect to the vtApp market place, search for vtApps, buy/pay them and directly download and install them.</b>";
		$output.= "<br/><h2>On sale soon at http://evoshops.com</h2>";
		$output.= "<iframe style='width:98%;height:84%;margin:auto;' src='http://evoshops.com'/>";
		return $output;
	}

}
?>
