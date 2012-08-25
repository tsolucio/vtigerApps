<div id="editgrid" class='comtsoluciolistview_editbox' style="display:{$LVPINDISPLAY}">
<span>
&nbsp;{$APP.LBL_MODULE}&nbsp;{html_options name=modulename id=modulename class=small options=$LVMODULE_OPTION selected=$LVMODULE}
&nbsp;&nbsp;&nbsp;{$APP.LBL_VIEW}&nbsp;<SELECT NAME="viewname" id="viewname" class="small">{$CUSTOMVIEW_OPTION}</SELECT>
&nbsp;&nbsp;&nbsp;&nbsp;{$LBL_HOME_SHOW}&nbsp;<input type="number" id="lvpagesize" name="lvpagesize" size=3 maxlength=3 value="{$gridPageSize}">&nbsp;{$LBL_HOME_ITEMS}
</span>
<span style="text-align:right;"><img align=right width=16px; id="editpin" src="{$LVPINNED}"></span>
</div>
<div id="grid" style='height:98%;'></div>
<div id="gridData" style="display:none">{$kendocols}</div>