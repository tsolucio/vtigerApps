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

class ComTsolucioKeyMetrics extends vtAppBase {

  public $lv_editpinned = false;
  public $startDate;
  public $endDate;
  public $users;

	public function getContent($onlydata=false) {
	  global $adb, $current_user;

	  require('user_privileges/user_privileges_'.$current_user->id.'.php');

	  $ssql = "select vtiger_customview.* from vtiger_customview inner join vtiger_tab on vtiger_tab.name = vtiger_customview.entitytype where vtiger_customview.setmetrics = 1";
	  if($is_admin == false){
	    $ssql .= " and (vtiger_customview.status=0 or vtiger_customview.userid = {$current_user->id} or vtiger_customview.status =3 or vtiger_customview.userid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '".$current_user_parent_role_seq."::%'))";
	  }
	  $ssql .= " order by vtiger_customview.entitytype";
	  $result = $adb->query($ssql);
	  $metricslists = array();
	  while($cvrow=$adb->fetch_array($result)) {
	    $metricslist = Array();
	    if(vtlib_isModuleActive($cvrow['entitytype'])){
	      $metricslist['id'] = $cvrow['cvid'];
	      $metricslist['name'] = $cvrow['viewname'];
	      $metricslist['module'] = $cvrow['entitytype'];
	      $metricslist['user'] = getUserName($cvrow['userid']);
	      if(isPermitted($cvrow['entitytype'],"index") == "yes"){
	        $metriclists[] = $metricslist;
	      }
	    }
	  }

	  $subordinateRoleAndUsers = getSubordinateRoleAndUsers(fetchUserRole($current_user->id));
	  $subordinateUsers = array();
	  foreach($subordinateRoleAndUsers as $roleId=>$users) {
	    foreach($users as $userId=>$userName) {
	      if (!in_array($userId, $subordinateUsers)) {
	        $subordinateUsers[] = $userId;
	      }
	    }
	  }
	  if (!in_array($current_user->id, $subordinateUsers)) {
	    $subordinateUsers[] = $current_user->id;
	  }
	  
	  if (!empty($this->users)) {
	    foreach($this->users as $k=>$userId) {
	      if (!in_array($userId, $subordinateUsers)) {
	        unset($this->users[$k]);
	      }
	    }
	    $users = $this->users;
	  }
	  else {
	    $users = $subordinateUsers;
	  }

	  $data = array();
	  foreach($users as $userId) {
	    $user = CRMEntity::getInstance('Users');
	    $user = $user->retrieve_entity_info($userId, 'Users');
	    $userName = getUserFullName($userId); // $user->column_fields['user_name'];
	    $item = array(
	      'id' => $userId,
	      'username' => $userName,
	      'startdate' => DateTimeField::convertToUserFormat($this->startDate, $current_user),
	      'enddate' => DateTimeField::convertToUserFormat($this->endDate, $current_user),
	      );
	    foreach ($metriclists as $key => $metriclist) {
	      $tmpCurrentUser = $current_user;
	      $current_user = $user;
	      if ($metriclist['module'] == "Calendar") {
	        $listquery = getListQuery($metriclist['module']);
	        $oCustomView = new CustomView($metriclist['module']);
	        $metricsql = $oCustomView->getModifiedCvListQuery($metriclist['id'],$listquery,$metriclist['module']);
	      }
	      else {
	        $queryGenerator = new QueryGenerator($metriclist['module'], $user);
	        $queryGenerator->initForCustomViewById($metriclist['id']);
	        $metricsql = $queryGenerator->getQuery();
	      }
	      $current_user = $tmpCurrentUser;
	      //echo $metricsql, "<br>";
	      $metricsql = preg_replace('/\( *([\w_]+\.[\w_]+) +BETWEEN +\'\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d\' +AND +\'\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d\' *\)/', "($1 BETWEEN '{$this->startDate} 00:00:00' AND '{$this->endDate} 23:59:59')", $metricsql);
	      $metricsql = preg_replace('/\( *([\w_]+\.[\w_]+) +BETWEEN +\'\d\d\d\d-\d\d-\d\d\' +AND +\'\d\d\d\d-\d\d-\d\d\' *\)/', "($1 BETWEEN '{$this->startDate}' AND '{$this->endDate}')", $metricsql);
	      //echo $metricsql, "<br>";
	      $metricsql = mkCountQuery($metricsql);
	      $metricresult = $adb->query($metricsql);
	      if ($metricresult) {
	        $rowcount = $adb->fetch_array($metricresult);
	        $item['col_'.$key] = intval($rowcount['count']);
	      }
	    }
	    $data[] = $item;
	  }

	  $columns = array(
	    array('field' => 'username', 'width' => 100, 'title' => $this->translate('Asesores')),
	    array('field' => 'startdate', 'width' => 100, 'title' => $this->translate('Inicio')),
	    array('field' => 'enddate', 'width' => 100, 'title' => $this->translate('Fin')),
	    );
	  $aggregate = array();
	  foreach($metriclists as $key=>$metriclist) {
	    $columns[] = array(
	      'field' => 'col_'.$key,
	      'width' => mb_strlen($metriclist['name'])*6+10,
	      'title' => $metriclist['name'],
	      'footerTemplate' => $this->translate('Total:').' #=sum#',
	      );
	    $aggregate[] = array(
	      'field' => 'col_'.$key,
	      'aggregate' => 'sum',
	      );
	  }

	  $usersOptions = array();
	  foreach($subordinateUsers as $subordinateUserId) {
	    $usersOptions[$subordinateUserId] = getUserFullName($subordinateUserId);
	  	//$adb->getOne("select user_name from vtiger_users where id={$subordinateUserId}");
	  }

	  $dateFormat = str_replace('mm', 'MM', $current_user->date_format);
	  $startDate = DateTimeField::convertToUserFormat($this->startDate, $current_user);
	  $endDate = DateTimeField::convertToUserFormat($this->endDate, $current_user);

	  if ($onlydata) {
	  	return array(
	  			'startdate'=>$startDate,
	  			'endDate'=>$endDate,
	  			'users'=>$usersOptions,
	  			'data'=>$data,
	  			'columns'=>$columns
	  			);
	  }
		$template = 'template.php';
		ob_start();
		require($this->getPath($template));
		$output = ob_get_clean();
		return $output;
	}

	function getPin() {
	  return $this->lv_editpinned;
	}

	public function changePin() {
		$this->lv_editpinned = !$this->lv_editpinned;
		$imgpath=$this->getPath(($this->lv_editpinned ? 'pin_disabled.gif':'pin_enabled.gif'));
		return json_encode($imgpath);
	}

	public function setFilter($startDate,$endDate,$users) {
	  $dbStartDate = DateTimeField::convertToDBFormat($startDate);
	  $dbEndDate = DateTimeField::convertToDBFormat($endDate);
		$this->startDate = $dbStartDate;
		$this->endDate = $dbEndDate;
		$this->users = $users;
	}

	public function getExcelExport() {
		header("Content-Type: application/excel");
		header("Content-Type: application/download");
		header("Pragma: public");
		header("Cache-Control: private");
		header("Content-Disposition: attachment; filename=keymetric.xls");
		header("Content-Description: vtapps download");
		$info = $this->getContent(true);
		$xls = $info['startdate'].';'.$info['endDate'].';';
		foreach($info['users'] as $userId=>$userName) {
			$xls.=$userName.';';
		}
		$xls.="\n";
		foreach($info['columns'] as $colinfo) {
			$xls.=$colinfo['title'].';';
		}
		$xls.="\n";
		$aggs = array();
		foreach($info['data'] as $dataRow) {
			foreach ($dataRow as $key => $value) {
				if ($key=='id') continue;
				if (is_numeric($value)) {
					$aggs[$key] = $aggs[$key] + $value;
				}
				$xls.=$value.';';;
			}
		}
		$xls.="\n";
		$xls.=";;;";
		foreach($aggs as $aggregate) {
			$xls.=$aggregate.';';
		}
		$xls.="\n";
		return $xls;
	}
}
?>
