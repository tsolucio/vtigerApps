<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/

class ComTsolucioPotentialSalesStage extends vtAppBase {

	public function getContent() {
		global $adb,$log;
		$smarty = new vtigerCRM_Smarty;
		$smarty->template_dir = $this->getPath();
		$smarty->assign("Title",$this->translate('Title'));
		$potstotal=$adb->getone('SELECT count(*) FROM vtiger_potential inner join vtiger_crmentity on crmid = potentialid and deleted=0');
		$rspotsmax=$adb->query('SELECT sales_stage, count(*) FROM vtiger_potential inner join vtiger_crmentity on crmid = potentialid and deleted=0 GROUP BY sales_stage ORDER BY 2 DESC LIMIT 1');
		$potsmax=$adb->fetch_array($rspotsmax);
		$rspots=$adb->query('SELECT sales_stage, count(*) as cnt FROM vtiger_potential inner join vtiger_crmentity on crmid = potentialid and deleted=0 GROUP BY sales_stage');
		$data = array();
		while ($pt=$adb->fetch_array($rspots)) {
			$data[] = array($pt['sales_stage'], $pt['cnt']*100/$potstotal);
		}
		$smarty->assign("PotData",json_encode($data));
		return $smarty->fetch('piechart.tpl');
	}

}
?>