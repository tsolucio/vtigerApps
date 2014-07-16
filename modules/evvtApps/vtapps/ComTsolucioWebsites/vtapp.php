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

class VtApp_ComTsolucioWebsites extends VtAppBase {

	public $website;

	public function getContent() {		
		return '
		<div id="urldiv" style="width:100%;display:none;text-align:center;margin-bottom:6px;">&nbsp;'.$this->translate('website').'&nbsp;<input type="text" id="urlname" name="urlname" value="'.$this->website.'">&nbsp;<input type="button" id="urlbutton" value="'.$this->translate('saveurl').'" class="crmButton small save"></div>
		<iframe src="'.$this->website.'" style="width:100%;height:100%"></iframe>';
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
