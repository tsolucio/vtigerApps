<form id="app-selector" class="com-tsolucio-Admin-app-selector">
  <div class="com-tsolucio-Admin-app-icon-div">
    <img id="app-icon" width="64" height="64" />
  </div>
  <div id="app-description" class="com-tsolucio-Admin-app-description"></div>
  <div>
    &nbsp;<select style="vertical-align:middle;" id="app-id">
      <option value=""><?php echo $this->translate('Choose app...'); ?></option>
      <?php
      foreach($launchers as $launcher) {
        echo "<option value=\"{$launcher->getId()}\">{$launcher->getName()}</option>";
      }
      ?>
    </select>
  </div>
</form>
<div id="grid" style="clear:both;"></div>