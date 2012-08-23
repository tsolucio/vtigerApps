<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
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
