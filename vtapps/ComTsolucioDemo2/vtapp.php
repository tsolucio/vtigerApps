<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  JPL TSolucio, S.L. Open Source
 * The Initial Developer of the Original Code is JPL TSolucio, S.L.
 * Portions created by JPL TSolucio, S.L. are Copyright (C) JPL TSolucio, S.L.
 * All Rights Reserved.
 ************************************************************************************/

class VtApp_ComTsolucioDemo2 extends vtAppBase {

	public function getContent() {
		global $adb;
		$smarty = new vtigerCRM_Smarty;
		$smarty->template_dir = $this->getPath();
		$smarty->assign("Title",$this->translate('Title'));
		$potstotal=$adb->getone('SELECT count(*) FROM vtiger_potential');
		$rspotsmax=$adb->query('SELECT sales_stage, count(*) FROM vtiger_potential GROUP BY sales_stage ORDER BY 2 DESC LIMIT 1');
		$potsmax=$adb->fetch_array($rspotsmax);
		$rspots=$adb->query('SELECT sales_stage, count(*) as cnt FROM vtiger_potential GROUP BY sales_stage');
		$data = array();
		while ($pt=$adb->fetch_array($rspots)) {
			if ($potsmax['sales_stage']==$pt['sales_stage']) {
				$data[] = array(
				  'name' => $pt['sales_stage'],
				  'y' => $pt['cnt']*100/$potstotal,
				  'sliced' => true,
				  'selected' => true);
			} else {
				$data[] = array($pt['sales_stage'], $pt['cnt']*100/$potstotal);
			}
		}
		$smarty->assign("PotData",json_encode($data));
		return $smarty->fetch('piechart.tpl');
	}

}
?>