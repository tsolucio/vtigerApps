<?php
/*************************************************************************************************
 * Copyright 2012 JPL TSolucio, S.L. -- This file is a part of evvtApps.
 * Licensed under the GNU General Public License (the "License"); you may not use this
 * file except in compliance with the License. You can redistribute it and/or modify it
 * under the terms of the License. JPL TSolucio, S.L. reserves all rights not expressly
 * granted by the License. vtiger CRM distributed by JPL TSolucio S.L. is distributed in
 * the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Unless required by
 * applicable law or agreed to in writing, software distributed under the License is
 * distributed on an "AS IS" BASIS, WITHOUT ANY WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific language governing
 * permissions and limitations under the License. You may obtain a copy of the License
 * at <http://www.gnu.org/licenses/>
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/

global $currentModule,$current_user,$log;

if (!empty($_REQUEST['searchinmodules'])) {
	$searchin = explode('#', $_REQUEST['searchinmodules']);
} else {
	$searchin = array('Accounts','Contacts','HelpDesk','Project','ProjectMilestone','ProjectTask');
}
$respuesta=array();

if (empty($_REQUEST['filter']['filters'][0]['value'])) {
	$aname='%';
	$op='like';
} else {
	$aname=$_REQUEST['filter']['filters'][0]['value'];
	switch ($_REQUEST['filter']['filters'][0]['operator']) {
		case 'eq':
			$op='=';
			break;
		case 'neq':
			$op='!=';
			break;
		case 'startswith':
			$aname=$aname.'%';
			$op='like';
			break;
		case 'endswith':
			$aname='%'.$aname;
			$op='like';
			break;
		case 'contains':
			$op='like';
			$aname='%'.$aname.'%';
			break;
		default: $op='='; break;
	}
}

foreach ($searchin as $srchmod) {
	if (!(vtlib_isModuleActive($srchmod) and isPermitted($srchmod,'DetailView'))) continue;
	$eirs = $adb->pquery('select fieldname,tablename,entityidfield from vtiger_entityname where modulename=?',array($srchmod));
	$ei = $adb->fetch_array($eirs);
	$fieldsname = $ei['fieldname'];
	$wherefield = $ei['fieldname']." $op '$aname' ";
	if (!(strpos($fieldsname, ',') === false)) {
		$fieldlists = explode(',', $fieldsname);
		$fieldsname = "concat(";
		$fieldsname = $fieldsname . implode(",' ',", $fieldlists);
		$fieldsname = $fieldsname . ")";
		$wherefield = implode(" $op '$aname' or ", $fieldlists)." $op '$aname' ";
	}
	$qry = "select crmid,$fieldsname as crmname
			from {$ei['tablename']}
			inner join vtiger_crmentity on crmid = {$ei['entityidfield']}
			where deleted = 0 and ($wherefield)";
	$rsemp=$adb->query($qry);
	$trmod = getTranslatedString($srchmod,$srchmod);
	while ($emp=$adb->fetch_array($rsemp)) {
		$respuesta[]=array(
				'crmid'=>$emp['crmid'],
				'crmname'=>$emp['crmname']." :: $trmod",
				'crmmodule'=>$srchmod,
		);
	}
}
echo json_encode($respuesta);
