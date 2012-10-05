<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/

class ComTsolucioKeyMetrics extends vtAppBase {
	
  public $lv_editpinned = false;
  public $startDate;
  public $endDate;
  public $users;
  
	public function getContent() {
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
	  
	  $subordinateUsers = array_merge(array($current_user->id), getSubordinateUsersList());
	  
	  if (empty($this->users)) {
	    $users = $subordinateUsers;
	  }
	  else {
	    $users = $this->users;
	  }
	  
	  $data = array();
	  foreach($users as $userId) {
	    $user = CRMEntity::getInstance('Users');
	    $user = $user->retrieve_entity_info($userId, 'Users');
	    $userName = $user->column_fields['user_name'];
	    $item = array(
	      'id' => $userId,
	      'username' => $userName,
	      'startdate' => $this->startDate,
	      'enddate' => $this->endDate,
	      );
	    foreach ($metriclists as $key => $metriclist) {
	      $queryGenerator = new QueryGenerator($metriclist['module'], $user);
	      $queryGenerator->initForCustomViewById($metriclist['id']);
	      $metricsql = $queryGenerator->getQuery();
	      //echo $metricsql, "<br>";
	      $metricsql = preg_replace('/\( *([\w_]+\.[\w_]+) +BETWEEN +\'\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d\' +AND +\'\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d\' *\)/', "($1 BETWEEN '{$this->startDate}' AND '{$this->endDate}')", $metricsql);
	      //echo $metricsql, "<br>";
	      $metricsql = mkCountQuery($metricsql);
	      $metricresult = $adb->query($metricsql);
	      if($metricresult) {
	        if($metriclist['module'] == "Calendar") {
	          $item['col_'.$key] = intval($adb->num_rows($metricresult));
	        } else {
	          $rowcount = $adb->fetch_array($metricresult);
	          $item['col_'.$key] = intVal($rowcount['count']);
	        }
	      }
	    }
	    $data[] = $item;
	  }
	  
	  $columns = array(
	    array('field' => 'username', 'title' => $this->translate('Asesores')),
	    array('field' => 'startdate', 'title' => $this->translate('Inicio')),
	    array('field' => 'enddate', 'title' => $this->translate('Fin')),
	    );
	  $aggregate = array();
	  foreach($metriclists as $key=>$metriclist) {
	    $columns[] = array(
	      'field' => 'col_'.$key,
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
	    $usersOptions[$subordinateUserId] = $adb->getOne("select user_name from vtiger_users where id={$subordinateUserId}");
	  }
	  
	  $dateFormat = str_replace('mm', 'MM', $current_user->date_format);
	  $startDate = DateTimeField::convertToUserFormat($this->startDate);
	  $endDate = DateTimeField::convertToUserFormat($this->endDate);
	  
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

}
?>
