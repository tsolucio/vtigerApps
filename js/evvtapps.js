/**
 * evvtApps javascript code
 * Copyright 2012  JPL TSolucio, S.L.
 */


function evvtappsOpenWindow(appid,classname,appinfo) {
	arrAction = new Array();
	elements=0;
	if (appinfo.hasEdit) arrAction[elements++]="Edit";
	if (appinfo.hasRefresh) arrAction[elements++]="Refresh";
	//if (appinfo.hasSize) arrAction[elements++]="Minimize";
	if (appinfo.hasSize) arrAction[elements++]="Maximize";
	arrAction[elements++]="Close";
	windowname='vtapp'+appid;
	if ($('#'+windowname).length==0) {  // doesn't exist yet, we have to create it
		$('#evvtCanvas').append('<div id="'+windowname+'"></div>');
		$('#'+windowname).kendoWindow({
			draggable: true,  // all windows are draggable
			resizable: appinfo.hasSize,
			visible: true,  // we are opening it!
			width: appinfo.wWidth+"px",
			height: appinfo.wHeight+"px",
			title: appinfo.appTitle,
			modal: false,  // no window is fixed
			actions: arrAction,
			content: 'index.php?module=evvtApps&action=evvtAppsAjax&file=vtappaction&vtappaction=getAbout&class='+classname+'&appid='+appid
		 });
	} else {  // we put it on top
		appwindow=$('#'+windowname).data("kendoWindow");
		appwindow.toFront();
	}
}
