<div id="qtcinputs" class="watch{if $lastTC.timecontrolid eq ''}off{else}on{/if}">
<div class="left task-form-bottom-fields" style="margin-top: 0px;">
<h2>{$vtAPP.trackingtime}</h2>
<input id="tcid" type="hidden" value="{$lastTC.timecontrolid}">
<input type="text" id="uitype10" class="work-field" value="{$lastTC.relatedname}"/><input id="workoncrmid" type="hidden" value="{$lastTC.relatedto}">&nbsp;&nbsp;
<input type="text" id="ttime" class="ttime" value=""/><input id="ttime_seconds" type="hidden" value="{$lastTC.totaltime}">&nbsp;&nbsp;
<input type="button" id="tcbtn" value="{if $lastTC.timecontrolid eq ''}{$vtAPP.LBL_WATCH_START}{else}{$vtAPP.LBL_WATCH_STOP}{/if}" class="{if $lastTC.timecontrolid eq ''}start{else}stop{/if}-button"><br/>
</div>
<div class="left task-form-bottom-fields">
<div class="clear"></div>
<div class="inputbox left time-container">
  <input class="task-time-start" type="text" id="starttime" value="{$lastTC.time_start}" tabindex="104" disabled="">
  <div class="left input-hint" data-translate-html="{'Time Start'|@getTranslatedString:'Timecontrol'}">{$vtAPP.Start}</div>
</div>
<div class="inputbox left time-container">
  <input class="task-time-stop" type="text" id="stoptime" value="{$lastTC.time_end}" tabindex="105" disabled=""><input id="stoptimesecs" type="hidden" value="{$lastTC.time_endsecs}">
  <div class="left input-hint" data-translate-html="{'Time End'|@getTranslatedString:'Timecontrol'}">{$vtAPP.End}</div>
</div>
<div class="inputbox date-input-container left">
  <input class="todays-date" type="text" id="startdate" value="{$lastTC.date_start}" disabled="">
  <div class="left input-hint" data-translate-html="{'Date Start'|@getTranslatedString:'Timecontrol'}">{'Date Start'|@getTranslatedString:'Timecontrol'}</div>
</div>
<div class="inputbox left concept-container">
  <select class="small" tabindex="104" id="tcrelconcept">
    <option value=""></option>
	{foreach item=arr from=$fldvalue}
		{if $arr[0] eq $APP.LBL_NOT_ACCESSIBLE}
		<option value="{$arr[0]}" {$arr[2]}>{$arr[0]}</option>
		{else}
		<option value="{$arr[1]}" {$arr[2]}>{$arr[0]}</option>
		{/if}
	{/foreach}
  </select>
  <div class="left input-hint" data-translate-html="{'Related Concept'|@getTranslatedString:'Timecontrol'}">{'Related Concept'|@getTranslatedString:'Timecontrol'}</div>
</div>
<div class="inputbox left bill-container">
  <input class="left bill-field" id="billwith" type="text" value="{$lastTC.product_name}" tabindex="105"><input id="billwithcrmid" type="hidden" value="{$lastTC.product_id}">
  <div class="clear"></div>
  <div class="left input-hint" data-translate-html="{$vtAPP.BillWith}">{$vtAPP.BillWith}</div>
</div>
<div class="clear"></div>
</div>
<div class="clear"></div>
<br/><br/>
</div>
<div class="clear"></div>
<br/>
<div id="grid" style='margin:auto;'></div>
