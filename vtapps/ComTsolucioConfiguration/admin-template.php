<form id="app-selector" class="com-tsolucio-Admin-app-selector">
  <div class="com-tsolucio-Admin-app-icon-div">
    <img id="app-icon" width="64" height="64" />
  </div>
  <div id="app-description" class="com-tsolucio-Admin-app-description"></div>
  <div>
    &nbsp;<img style="vertical-align:middle;" id="addvtapp" alt="<?php echo $this->translate('Install/Upgrade app...'); ?>" title="<?php echo $this->translate('Install/Upgrade app...'); ?>" src="<?php echo $this->getPath('btnL3Add.gif'); ?>">
    &nbsp;<select style="vertical-align:middle;" id="app-id">
      <option value=""><?php echo $this->translate('Choose app...'); ?></option>
      <?php
      foreach($launchers as $launcher) {
        echo "<option value=\"{$launcher->getId()}\">{$launcher->getName()}</option>";
      }
      ?>
    </select>
    &nbsp;<img style="display:none;vertical-align:middle;" id="delvtapp" alt="<?php echo $this->translate('Delete app...'); ?>" title="<?php echo $this->translate('Delete app...'); ?>" src="<?php echo $this->getPath('trash_28.png'); ?>">
  </div>
</form>
<div id="vtupld" style="display:none;width:96%;margin:4px auto;">
<h2><?php echo $this->translate('InstallUpgradeMessage'); ?></h2>
<input name="vtupload" id="vtupload" type="file" />
<div id="nokResult" class="com-tsolucio-errorSummary" style="display:none;"></div>
<div id="okResult" class="com-tsolucio-okSummary" style="display:none;"></div>
</div>
<div id="grid" style="clear:both;"></div>