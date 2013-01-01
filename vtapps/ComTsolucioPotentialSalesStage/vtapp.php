<?php
/*************************************************************************************************
 * Copyright 2012 JPL TSolucio, S.L. -- This file is a part of TSOLUCIO vtiger CRM Customizations.
 * You can copy, adapt and distribute the work under the "Attribution-NonCommercial-ShareAlike"
 * Vizsage Public License (the "License"). You may not use this file except in compliance with the
 * License. Roughly speaking, non-commercial users may share and modify this code, but must give credit
 * and share improvements. However, for proper details please read the full License, available at
 * http://vizsage.com/license/Vizsage-License-BY-NC-SA.html and the handy reference for understanding
 * the full license at http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html. Unless required by
 * applicable law or agreed to in writing, any software distributed under the License is distributed
 * on an  "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the
 * License terms of Creative Commons Attribution-NonCommercial-ShareAlike 3.0 (the License).
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
			$data[] = '{ label: "'.$pt['sales_stage'].'", data: '.$pt['cnt']*100/$potstotal.'}';
		}
		$smarty->assign("PotData",'var chartData = ['.implode(',',$data).'];');
		return $smarty->fetch('piechart.tpl');
	}

}
?>