<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/

class VtApp_ComTsolucioWebsites extends VtAppBase {

	public $website;

	public function getContent() {		
		return '
		<div id="urldiv" style="width:100%;display:none;text-align:center;margin-bottom:6px;">&nbsp;'.$this->translate('website').'&nbsp;<input type="text" id="urlname" name="urlname" value="'.$this->website.'">&nbsp;<input type="button" id="urlbutton" value="'.$this->translate('saveurl').'" class="crmButton small save"></div>
		<iframe src="'.$this->website.'" width=100% height=100%></iframe>';
	}

	public function getTitle() {
		return (empty($this->website) ? $this->translate('appName') : $this->website);
	}

	public function setWebsite($url) {
		$url = strtolower(trim($url));
		if (substr($url,0,4) != 'http') $url = 'http://'.$url;
		$this->website = $url;
	}
}
?>
