<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  JPL TSolucio, S.L. Open Source
 * The Initial Developer of the Original Code is JPL TSolucio, S.L.
 * Portions created by JPL TSolucio, S.L. are Copyright (C) JPL TSolucio, S.L.
 * All Rights Reserved.
 ************************************************************************************/

class vtAppcomTSolucioDemoApp extends vtApp {
	
	var $hasedit = true;
	var $hasrefresh = true;
	var $hassize = true;
	var $candelete = true;
	var $wwidth = 800;
	var $wheight = 400;
	var $haseditsize = true;
	var $ewidth = 0;
	var $eheight = 0;
	
	public function getContent($lang) {		
		global $adb,$current_language,$current_user;
		$smarty = new vtigerCRM_Smarty;
		$smarty->template_dir = $this->apppath;
		$smarty->assign('appId',$this->appid);
		$smarty->assign('appPath',$this->apppath);
		$smarty->assign("Title",$this->getvtAppTranslatedString('Title', $current_language));
		$potstotal=$adb->getone('SELECT count(*) FROM vtiger_potential');
		$rspotsmax=$adb->query('SELECT sales_stage, count(*) FROM vtiger_potential GROUP BY sales_stage ORDER BY 2 DESC LIMIT 1');
		$potsmax=$adb->fetch_array($rspotsmax);
		$rspots=$adb->query('SELECT sales_stage, count(*) as cnt FROM vtiger_potential GROUP BY sales_stage');
		$data='[';
		while ($pt=$adb->fetch_array($rspots)) {
			if ($potsmax['sales_stage']==$pt['sales_stage']) {
				$data.="{name:'".$pt['sales_stage']."',y:".($pt['cnt']*100/$potstotal).',sliced:true, selected:true},';
			} else {
				$data.="['".$pt['sales_stage']."',".($pt['cnt']*100/$potstotal).'],';
			}
		}
		$data=trim($data,',').']';
		$smarty->assign("PotData",$data);
		return $smarty->fetch('piechart.tpl');
	}

}
?>
