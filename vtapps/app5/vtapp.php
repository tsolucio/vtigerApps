<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  JPL TSolucio, S.L. Open Source
 * The Initial Developer of the Original Code is JPL TSolucio, S.L.
 * Portions created by JPL TSolucio, S.L. are Copyright (C) JPL TSolucio, S.L.
 * All Rights Reserved.
 ************************************************************************************/

class vtAppcomTSolucioDemoGraph1 extends vtApp {
	
	var $hasedit = false;
	var $hasrefresh = false;
	var $hassize = false;
	var $candelete = false;
	var $wwidth = 450;
	var $wheight = 450;

	public function getContent($lang) {
		$output='<div id="vtappchart1app'.$this->appid.'"></div><script>
            $("#vtappchart1app'.$this->appid.'").kendoChart({
                        title: {
                            text: "'.$this->getTitle($lang).'"
                        },
                        series: [
                            {
                                name: "Series 1",
                                data: [200, 450, 300, 125],
                                type: "pie"
                            }
                        ],
                        categoryAxis: {
                            categories: [2000, 2001, 2002, 2003]
                        }
                    });
                    </script>';
		return $output;
	}

}
?>
