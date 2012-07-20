<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  JPL TSolucio, S.L. Open Source
 * The Initial Developer of the Original Code is JPL TSolucio, S.L.
 * Portions created by JPL TSolucio, S.L. are Copyright (C) JPL TSolucio, S.L.
 * All Rights Reserved.
 ************************************************************************************/

class VtApp_ComTsolucioDemoGraph1 extends vtAppBase {
	
	var $hasedit = false;
	var $hasrefresh = true;
	var $hassize = true;
	var $candelete = false;
	var $wwidth = 850;
	var $wheight = 850;

	public function getContent() {
		$output='
<script id="template" type="text/x-kendo-template">
 # if (TYPE == "create") { #
    <tr style="background-color:LightGreen" class="k-grouping-rows">
  #  } else if (TYPE == "update"){ #
    <tr class="k-grouping-rows">
#  } else if (TYPE.indexOf("conflict")!=-1){ #
    <tr style="background-color:Khaki" class="k-grouping-rows">
 #  } else { #
    <tr style="background-color:LightSalmon" class="k-grouping-rows">
# } #
#= new Array(this.group().length + 1).join(\'<td class="k-group-cell"></td>\') #
        <td>#= ID #</td>
        <td>#= MODULE #</td>
        <td>#= FROM #</td>
        <td>#= TO #</td>
        <td>#= DATE #</td>
        <td>#= TYPE #</td>
        <td>#= ERROR #</td>
    </tr>
</script>
<div id="example" class="k-content">
<div id="clientsDb" class="clientsDb">
<table id="grid" border=0 cellspacing=1 cellpadding=3 class="lvt small" width=70%>
</table>
</div>
</div>		';
		return $output;
	}

}
?>
