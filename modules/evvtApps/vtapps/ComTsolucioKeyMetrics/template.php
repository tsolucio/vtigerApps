<div id="editgrid" class="comtsoluciokeymetrics_editbox" style="display: <?php echo $this->getPin()? 'block' : 'none'; ?>">
<span style="text-align:right;"><img align="right" width="16px" style="padding:4px" id="editpin" src="<?php echo $this->getPath($this->getPin()? 'pin_disabled.gif' : 'pin_enabled.gif'); ?>"><img align="right" width="16px" id="expxls" style="padding:4px" src="<?php echo $this->getPath('xls-file.jpg'); ?>"></span>
<?php echo $this->translate('Start Date:'); ?>
<input id="startdate-picker" format="<?php echo $dateFormat; ?>" value="<?php echo $startDate; ?>">
<?php echo $this->translate('End Date:'); ?>
<input id="enddate-picker" format="<?php echo $dateFormat; ?>" value="<?php echo $endDate; ?>">
<?php echo $this->translate('Users:'); ?>
<select id="users" multiple>
<?php
foreach($usersOptions as $userId=>$userName) {
  if (in_array($userId, $users)) {
    echo "<option value=\"{$userId}\" selected>{$userName}</option>";
  }
  else {
    echo "<option value=\"{$userId}\">{$userName}</option>";
  }
}
?>
</select>
<input id="refresh" type="button" value="<?php echo $this->translate('Save'); ?>">
</div>
<div id="grid" style="height: 680px"></div>
<div id="grid-data" style="display:none;"><?php echo json_encode($data); ?></div>
<div id="grid-columns" style="display:none;"><?php echo json_encode($columns); ?></div>
<div id="grid-aggregate" style="display:none;"><?php echo json_encode($aggregate); ?></div>
