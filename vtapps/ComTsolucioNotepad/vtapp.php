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
class VtApp_ComTsolucioNotepad extends vtAppBase {

	public $stuffid;

	public function __construct($launcher, $id) {
		parent::__construct($launcher, $id);
		$this->postInstantiate();
	}

	public function getContent() {
	  global $adb, $current_user, $current_language;
	  $query = "select * from vtiger_notebook_contents where userid={$current_user->id} and notebookid = {$this->stuffid} limit 1";
	  $res = $adb->query($query);
	  $notebookId = $adb->query_result($res, 0, 'notebookid');
	  $contents = $adb->query_result($res, 0, 'contents');
	  $modstr = return_module_language($current_language, 'Home');
	  return "
		<div style=\"height:90%;margin:both;\" id=\"notebook_div\" title=\"{$modstr['LBL_NOTEBOOK_TITLE']}\">
		<span id=\"notebook_contents\" style=\"width: 96%; white-space: pre;\"><pre>{$contents}</pre></span>
		</div>
		<textarea id=\"notebook_textarea\" onfocus=\"this.className='detailedViewTextBoxOn'\" rows=\"18\" style=\"display:none; width:96%; height:90%;margin:both;\" title=\"{$modstr['LBL_NOTEBOOK_SAVE_TITLE']}\">{$contents}</textarea>
		<span class=\"small\" style=\"padding-left: 10px;display: block;\" id=\"notebook_dbl_click_message\">
		<font color=\"grey\">
		{$modstr['LBL_NOTEBOOK_TITLE']}
		</font>
		</span>
		<span class=\"small\" style=\"padding-left: 10px;display: none;\" id=\"notebook_save_message\">
		<font color=\"grey\">
		{$modstr['LBL_NOTEBOOK_SAVE_TITLE']}
		</font>
		</span>";
	}
	
	public function postInstantiate() {
	  global $adb, $current_user, $log;
	  $stuffid = $adb->getone("select stuffid from vtiger_homestuff where stufftitle='vtAppNB:".$this->getId()."'");
	  if (empty($stuffid)) {
	  	$this->createNewNotePad();
	  } else {
	  	$this->stuffid = $stuffid;
	  }
	}
	  
	public function createNewNotePad() {
		global $adb, $current_user, $log;
		$stuffid=$adb->getUniqueId('vtiger_homestuff');
		$query = "select max(stuffsequence)+1 as seq from vtiger_homestuff";
		$res = $adb->query($query);
		$sequence = $adb->query_result($res, 0, 'seq');
		$query = "insert into vtiger_homestuff (stuffid, stuffsequence, stufftype, userid, visible, stufftitle) values ($stuffid, $sequence, 'Notebook', ".$current_user->id.", 0, 'vtAppNB:".$this->getId()."')";
		$res = $adb->query($query);
		$query = "insert into vtiger_notebook_contents values ($current_user->id, $stuffid, '')";
		$adb->query($query);
		$this->stuffid = $stuffid;
	}

	public function saveData($content) {
	  global $adb, $current_user, $log;
	  $query = "update vtiger_notebook_contents set contents='{$content}' where userid={$current_user->id} and notebookid = {$this->stuffid} limit 1";
	  $adb->query($query);
	}

	public function preDestroy() {
		global $adb, $current_user, $log;
		$adb->query('delete from vtiger_homestuff where stuffid='.$this->stuffid);
		$adb->query('delete from vtiger_notebook_contents where userid='.$current_user->id.' and notebookid='.$this->stuffid);
	}
}
?>
