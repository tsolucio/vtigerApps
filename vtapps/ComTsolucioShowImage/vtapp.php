<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/

class ComTsolucioShowImage extends VtAppBase {

	public $imageurl;
	public $companyinfo;

	public function getContent() {	
		global $adb,$site_URL,$log;	
		$ret = '<div id="urldiv" style="width:100%;display:none;text-align:center;margin-bottom:6px;"><input type="checkbox" name="companyinfo" id="companyinfo"'.($this->companyinfo ? ' checked' : '').'>&nbsp;'.$this->translate('company').'&nbsp;&nbsp;&nbsp;'.$this->translate('imageurl').'&nbsp;<input type="text" id="urlname" name="urlname" value="'.$this->imageurl.'" style="width:160px;">&nbsp;<input type="button" id="urlbutton" value="'.$this->translate('saveurl').'" class="crmButton small save"></div>';
		$ret.= '<div style="width:100%;height:100%;text-align:center;vertical-align:middle;"><img src="';
		if ($this->companyinfo) {
			$logoname = $adb->getone('select logoname from vtiger_organizationdetails');
			$ret = $ret.$site_URL.'/test/logo/'.$logoname;
		} else {
			$ret.= $this->imageurl;
		}
		$ret.= '"/></div>';
		return $ret;
	}

	public function getTitle() {
		global $adb,$log;
		if ($this->companyinfo) {
			$title = $adb->getone('select organizationname from vtiger_organizationdetails');
		} else {
			$title = basename($this->imageurl);
		}
		return $title;
	}

	public function setImageUrl($url) {
		$url = strtolower(trim($url));
		if (substr($url,0,4) != 'http') $url = 'http://'.$url;
		$this->imageurl = $url;
	}

	public function setCompanyInfo($value) {
		$this->companyinfo = $value;
	}

}
?>
