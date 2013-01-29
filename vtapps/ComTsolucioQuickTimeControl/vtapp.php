<?php
/*************************************************************************************************
 * Copyright 2012 JPL TSolucio, S.L. -- This file is a part of evvtApps.
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

class ComTsolucioQuickTimeControl extends vtAppBase {

	public function getContent() {
		global $adb,$app_strings,$current_language,$current_user,$log,$vtapps_strings;
		$smarty = new vtigerCRM_Smarty;
		$smarty->template_dir = $this->getPath();
		$smarty->assign('APP', $app_strings);
		include($this->getLanguageFilename());
		$smarty->assign('vtAPP', $vtapps_strings);
		if (empty($_REQUEST['evvtapps_param_0'])) {
			$lastTC = $this->getActiveTC(0);
		} else {
			$lastTC = $this->getActiveTC($_REQUEST['evvtapps_param_0']);
		}
		$smarty->assign('lastTC', $lastTC);
		$smarty->assign('fldvalue', $this->getTCConceptPicklist($lastTC['relconcept']));
		return $smarty->fetch('qtc.tpl');
	}

	public function getTCConceptPicklist($value) {
		global $adb,$app_strings,$current_language,$current_user,$log,$vtapps_strings;
		require_once 'modules/PickList/PickListUtils.php';
		$roleid=$current_user->roleid;
		$fieldname='relconcept';
		$picklistValues = getAssignedPicklistValues($fieldname, $roleid, $adb);
		$valueArr = explode("|##|", $value);
		$pickcount = 0;
		$options = array();
		if(!empty($picklistValues)){
			foreach($picklistValues as $order=>$pickListValue){
				if(in_array(trim($pickListValue),array_map("trim", $valueArr))){
					$chk_val = "selected";
					$pickcount++;
				}else{
					$chk_val = '';
				}
				$options[] = array(getTranslatedString($pickListValue,'Timecontrol'),$pickListValue,$chk_val);
			}
			if($pickcount == 0 && !empty($value)){
				$options[] =  array($app_strings['LBL_NOT_ACCESSIBLE'],$value,'selected');
			}
		}
		return $options;
	}

	public function getMyTCs() {
		global $adb, $current_user;
		$rdo = array();
		$q = "SELECT `timecontrolid`,`relconcept`,`date_start`,`time_start`,`date_end`,`time_end`,`relatedto`,`product_id`,`totaltime`
			FROM `vtiger_timecontrol`
			INNER JOIN vtiger_crmentity on crmid=timecontrolid and deleted = 0
			WHERE smownerid=? order by createdtime desc limit 30";
		$tc = $adb->pquery($q,array($current_user->id));
		while ($tcrow = $adb->fetch_array($tc)) {
			$vtnow=new DateTimeField($tcrow['date_start']);
			$tcrow['date_start']=$vtnow->getDisplayDate($current_user);
			$relatedname='';
			$product_name='';
			if (!empty($tcrow['relatedto'])) {
				$relmod=getSalesEntityType($tcrow['relatedto']);
				$en = getEntityName($relmod, array($tcrow['relatedto']));
				$relatedname="<a href='index.php?module=$relmod&action=DetailView&record={$tcrow['relatedto']}' target=_blank>".$en[$tcrow['relatedto']].'</a>';
			}
			if (!empty($tcrow['product_id'])) {
				$relmod=getSalesEntityType($tcrow['product_id']);
				$en = getEntityName($relmod, array($tcrow['product_id']));
				$product_name="<a href='index.php?module=$relmod&action=DetailView&record={$tcrow['product_id']}' target=_blank>".$en[$tcrow['product_id']].'</a>';
			}
			$tclink="<a href='index.php?module=Timecontrol&action=DetailView&record={$tcrow['timecontrolid']}' target=_blank>";
			list($tsh,$tsm)=explode(':', $tcrow['time_start']);
			list($teh,$tem)=explode(':', $tcrow['time_end']);
			$rdo[] = array(
				'tcid'=>$tcrow['timecontrolid'],
				'tcdate'=>$tclink.$tcrow['date_start'].'</a>',
				'horainifin'=>$tclink.$tsh.':'.$tsm.(empty($teh) ? '' : '-'.$teh.':'.$tem).'</a>',
				'tcrelto'=>$relatedname,
				'tcbillw'=>$product_name,
				'tctime'=>'<b>'.(empty($tcrow['totaltime']) ? '0' : $tcrow['totaltime']).'</b>',
				'tcopen'=>(empty($teh) ? '1' : '0'),
			);
		}
		return json_encode(array('results'=>$rdo,'total'=>count($rdo)));
	}

	public function getActiveTC($acttcid=0) {
		global $adb, $current_user, $log;
		if (empty($acttcid)) { // get My Lastest Open TC
		$q = "SELECT `timecontrolid`,`relconcept`,`date_start`,`time_start`,`date_end`,`time_end`,`relatedto`,`product_id`,`totaltime`
			FROM `vtiger_timecontrol`
			INNER JOIN vtiger_crmentity on crmid=timecontrolid and deleted = 0
			WHERE smownerid=? and time_end='' order by createdtime desc limit 1";
			$tc = $adb->pquery($q,array($current_user->id));
		} else {
			$q = "SELECT `timecontrolid`,`relconcept`,`date_start`,`time_start`,`date_end`,`time_end`,`relatedto`,`product_id`,`totaltime`
			FROM `vtiger_timecontrol`
			WHERE timecontrolid=?";
			$tc = $adb->pquery($q,array($acttcid));
		}
		if ($adb->num_rows($tc)>0) {
			$rdo = $adb->fetch_array($tc);
			$vtnow=new DateTimeField($rdo['date_start']);
			$rdo['date_start']=$vtnow->getDisplayDate($current_user);
			$vtnow=new DateTimeField($rdo['date_end']);
			$rdo['date_end']=$vtnow->getDisplayDate($current_user);
			$vtnow=new DateTimeField();
			$nowtime = $vtnow->getDisplayTime($current_user);
			$tssecs = $this->tcConvertTimeToSeconds($rdo['time_start']);
			$nwsecs = $this->tcConvertTimeToSeconds($nowtime);
			if ($nwsecs>$tssecs) {
				$rdo['totaltime']=$nwsecs-$tssecs;
			} else {
				$rdo['totaltime']=$nwsecs+(23 * 60 * 60 + 59 * 60 + 59)-$tssecs;
			}
			$rdo['time_end']=substr($nowtime,0,strpos($nowtime,':',3));
			$rdo['time_endsecs']=$this->tcConvertTimeToSeconds($nowtime);
			list($tsh,$tsm)=explode(':', $rdo['time_start']);
			$rdo['time_start']=$tsh.':'.$tsm;
			$rdo['relatedname']='';
			$rdo['product_name']='';
			if (!empty($rdo['relatedto'])) {
				$en = getEntityName(getSalesEntityType($rdo['relatedto']), array($rdo['relatedto']));
				$rdo['relatedname']=$en[$rdo['relatedto']];
			}
			if (!empty($rdo['product_id'])) {
				$en = getEntityName(getSalesEntityType($rdo['product_id']), array($rdo['product_id']));
				$rdo['product_name']=$en[$rdo['product_id']];
			}
		} else {
			$rdo = array(
				'timecontrolid'=>0,
				'relconcept'=>'',
				'date_start'=>'',
				'time_start'=>'',
				'date_end'=>'',
				'time_end'=>'',
				'time_endsecs'=>'0',
				'relatedto'=>'',
				'relatedname'=>'',
				'product_id'=>'',
				'product_name'=>'',
				'totaltime'=>'0'
			);
		}
		return $rdo;
	}

	public function saveMyTC() {
		global $adb, $current_user, $log;
		require_once("modules/Timecontrol/Timecontrol.php");
		
		$focus = new Timecontrol();
		if (!empty($_REQUEST['tcid'])) {
			$focus->retrieve_entity_info($_REQUEST['tcid'], 'Timecontrol');
			foreach($focus->column_fields as $fieldname => $val) {
				$focus->column_fields[$fieldname] = decode_html($focus->column_fields[$fieldname]);
			}
			$date = new DateTimeField(null);
			$focus->column_fields['description'] = decode_html($focus->column_fields['description']);
			if (!empty($_REQUEST['duplicateit']) and $_REQUEST['duplicateit']=='1') {  // open new one based on this one
				$focus->column_fields['date_start'] = $date->getDisplayDate($current_user);
				$focus->column_fields['time_start'] = $date->getDisplayTime($current_user);
				$focus->column_fields['date_end'] = '';
				$focus->column_fields['time_end'] = '';
				$focus->column_fields['totaltime'] = '';
				$focus->mode = '';
				unset($focus->id);
			} else {  // close it
				$focus->column_fields['date_end'] = $date->getDisplayDate($current_user);
				$focus->column_fields['time_end'] = $date->getDisplayTime($current_user);
				$focus->column_fields['relatedto'] = $_REQUEST['relto'];
				$focus->column_fields['relconcept'] = $_REQUEST['relcpt'];
				$focus->column_fields['product_id'] = $_REQUEST['billto'];
				$focus->mode = 'edit';
				$focus->id  = $_REQUEST['tcid'];
			}
		}
		else {
			$date = new DateTimeField(null);
			$focus->column_fields['date_start'] = $date->getDisplayDate($current_user);
			$focus->column_fields['time_start'] = $date->getDisplayTime($current_user);
			$focus->column_fields['date_end'] = '';
			$focus->column_fields['time_end'] = '';
			$focus->column_fields['tcunits'] = '1';
			$focus->column_fields['description'] = '';
			$focus->column_fields['relatedto'] = $_REQUEST['relto'];
			$focus->column_fields['relconcept'] = $_REQUEST['relcpt'];
			$focus->column_fields['product_id'] = $_REQUEST['billto'];
			$_REQUEST['assigntype'] = 'U';
			$focus->column_fields['assigned_user_id'] = $current_user->id;
			$focus->mode = '';
		}
		$focus->save('Timecontrol');
		return $focus->id;
	}

	public function tcConvertTimeToSeconds($tctime) {
		list($h,$m,$s) = explode(':', $tctime);
		if (empty($s)) $s = 0;
		return $h * 60 * 60 + $m * 60 + $s;
	}

}
?>