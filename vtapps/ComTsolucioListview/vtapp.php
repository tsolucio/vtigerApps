<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/

class VtApp_ComTsolucioListview extends vtAppBase {

	var $lv_module='Contacts';
	var $lv_filter=7;
	var $lv_pagesize=25;
	var $lv_editpinned=true;

	public function getContent($lang) {
		global $adb,$app_strings,$current_language,$current_user,$log;
		$smarty = new vtigerCRM_Smarty;
		$smarty->template_dir = $this->getPath();
		$smarty->assign('APP', $app_strings);
		$smarty->assign('LBL_HOME_SHOW', getTranslatedString('LBL_HOME_SHOW','Home'));
		$smarty->assign('LBL_HOME_ITEMS', getTranslatedString('LBL_HOME_ITEMS','Home'));
		$oCustomView = new CustomView($this->lv_module);
		$customviewcombo_html = $oCustomView->getCustomViewCombo($this->lv_filter);
		$smarty->assign('CUSTOMVIEW_OPTION',$customviewcombo_html);
		$smarty->assign('LVMODULE',$this->lv_module);
		$modarray = $this->getFilterEntityModules();
		$smarty->assign('LVMODULE_OPTION',$modarray);
		$smarty->assign('gridPageSize',$this->lv_pagesize);
		$smarty->assign('LVPINNED',$this->getPath(($this->lv_editpinned ? 'pin_disabled.gif':'pin_enabled.gif')));
		$smarty->assign('LVPINDISPLAY',($this->lv_editpinned ? 'block':'none'));
		$fields = $oCustomView->getColumnsListByCvid($this->lv_filter);
		$kendocols=array();
		foreach ($fields as $field) {
			$finfo = explode(':',$field);
			$mlinfo = explode('_',$finfo[3],2);
			$fieldlabel = $mlinfo[1];
			$fieldlabel = str_replace('_',' ',$fieldlabel);
			$module = $mlinfo[0];
			$kendocols[] = array(
					'field'=>$finfo[1],
					'title'=>getTranslatedString($fieldlabel,$module),
					'encoded'=>false
			);
		}
		$smarty->assign('kendocols',json_encode($kendocols));
		return $smarty->fetch('listview.tpl');
	}

	public function getListElements() {
		global $log,$adb,$current_user,$app_strings;
		$queryGenerator = new QueryGenerator($this->lv_module, $current_user);
		$queryGenerator->initForCustomViewById($this->lv_filter);
		$q = $queryGenerator->getQuery();
		//$w = $queryGenerator->getConditionalWhere();
		$totq = $q;
		$noofrows=$adb->getone(mkCountQuery($totq));
		if (isset($_REQUEST['sort'])) {
			$q.=' order by ';
			$ob='';
			foreach ($_REQUEST['sort'] as $sf) {
				$ob.=$sf['field'].' '.$sf['dir'].',';
			}
			$q.=trim($ob,',');
		}
		$start = 0;
		if (isset($_REQUEST['page']) and isset($_REQUEST['pageSize'])) {
			$start = ($_REQUEST['page']-1)*$_REQUEST['pageSize'];
			$q.=" limit $start, ".$_REQUEST['pageSize'];
		}
		$rs=$adb->query($q);
		$controller = new ListViewController($adb, $current_user, $queryGenerator);
		$focus = new $this->lv_module();
		$navigation_array = VT_getSimpleNavigationValues($start,$this->lv_pagesize,$noofrows);
		$listview_entries = $controller->getListViewEntries($focus,$this->lv_module,$rs,$navigation_array);
		$oCustomView = new CustomView($this->lv_module);		
		$fields = $oCustomView->getColumnsListByCvid($this->lv_filter);
		$fent = 0;
		$cols=array();
		foreach ($fields as $field) {
			$finfo = explode(':',$field);
			$cols[$fent] = $finfo[1];
			$fent++;
		}
		$ret=array();
		foreach ($listview_entries as $modid => $values) {
			$rec = array();
			foreach ($values as $key => $value) {
				$rec[$cols[$key]] = $value;
			}
			$ret['results'][]=$rec;
		}
		$ret['total'][]=$noofrows;
		if (empty($noofrows)) {
			$rec = array();
			$loop=1;
			foreach ($cols as $col) {
				$rec[$col] = ($loop==1 ? $app_strings['LBL_NO_DATA'] : '');
				$loop++;
			}
			$ret['results'][]=$rec;
		}
		return json_encode($ret);
	}

	public function getFilterEntityModules() {
		global $log;
		$tabrows = vtlib_prefetchModuleActiveInfo();
		$modulenamearr = array();
		foreach($tabrows as $resultrow) {
			if($resultrow['isentitytype'] != '0') {
				// Eliminate: Events, Emails
				if($resultrow['tabid'] == '16' || $resultrow['tabid'] == '10' || $resultrow['name'] == 'Webmails') {
					continue;
				}
				$modName=$resultrow['name'];
				if(isPermitted($modName,'ListView') == 'yes' && vtlib_isModuleActive($modName)){
					$modulenamearr[$modName]=getTranslatedString($modName,$modName);
				}
			}
		}
		asort($modulenamearr); // We avoided ORDER BY in Query (vtlib_prefetchModuleActiveInfo)!
		return $modulenamearr;
	}

	public function changeFilterList($module) {
		global $adb,$app_strings,$current_language,$current_user,$log;
		$oCustomView = new CustomView($module);
		$viewid = $oCustomView->getViewId($module);
		$this->lv_module=$module;
		$this->lv_filter=$viewid;
	}

	public function changePin() {
		$this->lv_editpinned = !$this->lv_editpinned;
		$imgpath=$this->getPath(($this->lv_editpinned ? 'pin_disabled.gif':'pin_enabled.gif'));
		return json_encode($imgpath);
	}

	public function getTitle() {
		if (empty($this->lv_filter)) {
			return $this->translate('appName');
		} else {
			global $app_strings;
			$oCustomView = new CustomView($this->lv_module);
			$viewnamedesc = $oCustomView->getCustomViewByCvid($this->lv_filter);
			$viewnamedesc = $viewnamedesc['viewname'];
			if($viewnamedesc == 'All') $viewnamedesc = 'COMBO_ALL';
			return getTranslatedString($this->lv_module,$this->lv_module).' :: '.getTranslatedString($viewnamedesc,$this->lv_module);
		}
	}

	public function setFilter($module,$filterid,$pagesize) {
		$this->lv_module=$module;
		$this->lv_filter=$filterid;
		$this->lv_pagesize=$pagesize;
	}
}
?>
