<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/
class VtApp_ComTsolucioNotepad extends vtAppBase {
	
	public function getContent() {
	  global $adb, $current_user, $current_language;
	  $query = "select * from vtiger_notebook_contents where userid={$current_user->id} order by notebookid asc limit 1";
	  $res = $adb->query($query);
	  $notebookId = $adb->query_result($res, 0, 'notebookid');
	  $contents = $adb->query_result($res, 0, 'contents');
	  $modstr = return_module_language($current_language, $module);
	  $className = get_class();
	  return "
	  <script type=\"text/javascript\">
	  function vtappAjax(vtappClass, vtappId, method, params) {
      var data = {
        module: 'evvtApps',
        action: 'evvtAppsAjax',
        file: 'vtappaction',
        class: vtappClass,
        appid: vtappId,
        vtappaction: 'dovtAppMethod',
        vtappmethod: method
      };
      for (attr in params) {
        data[attr] = params[attr];
      }
      $.ajax({
        url: 'index.php',
        data: data,
        success: function() {
          $('#vtapp'+vtappId).data('kendoWindow').refresh();
        }
      });
    }
	  function editContents(node, notebookid) {
      var notebook = $('#notebook_textarea_'+notebookid);
      var contents = $('#notebook_contents_'+notebookid);
      var notebook_dbl_click_message = $('#notebook_dbl_click_message');
      var notebook_save_message = $('#notebook_save_message');
      $(node).css('display', 'none');
      notebook.css('display', 'block');
      notebook_dbl_click_message.css('display', 'none');
      notebook_save_message.css('display', 'block');
      notebook.focus();
	  }
	  </script>
		<div style=\"height:100%;\" id=\"notebook_{$notebookId}\" ondblclick=\"editContents(this, {$notebookId});\" title=\"{$modstr['LBL_NOTEBOOK_TITLE']}\">
		<span id=\"notebook_contents_{$notebookId}\" style=\"width: 100%; white-space: pre;\"><pre>{$contents}</pre></span>
		</div>
		<textarea id=\"notebook_textarea_{$notebookId}\" onfocus=\"this.className='detailedViewTextBoxOn'\" rows=\"18\" onblur=\"vtappAjax('{$className}', {$this->appid}, 'saveData', { contents: this.value })\" style=\"display:none; width:100%; height:100%;\" title=\"{$modstr['LBL_NOTEBOOK_SAVE_TITLE']}\">{$contents}</textarea>
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
	
	public function postInstall() {
	  global $adb, $current_user;
	  $stuffid=$adb->getUniqueId('vtiger_homestuff');
	  $query = "select max(stuffsequence)+1 as seq from vtiger_homestuff";
	  $res = $adb->query($query);
		$sequence = $adb->query_result($res, 0, 'seq');
		$query = "insert into vtiger_homestuff (stuffid, stuffsequence, stufftype, userid, visible, stufftitle) values ($stuffid, $sequence, 'Notebook', $current_user->id, 0, 'Notebook')";
		$res = $adb->query($query);
		$query = "insert into vtiger_notebook_contents values ($current_user->id, $stuffid, '')";
		$adb->query($query);
	}

	public function saveData() {
	  global $adb, $current_user;
	  $query = "update vtiger_notebook_contents set contents='{$_REQUEST['contents']}' where userid={$current_user->id} order by notebookid asc limit 1";
	  $adb->query($query);
	}
}
?>
