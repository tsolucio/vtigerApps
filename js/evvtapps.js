/**
 * evvtApps javascript code
 * Copyright 2012  JPL TSolucio, S.L.
 */

var evvtURLp='module=evvtApps&action=evvtAppsAjax&file=vtappaction';

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
			vtappid: appid,
			vtclassname: appinfo.className,
			draggable: true,  // all windows are draggable
			resizable: appinfo.hasSize,
			visible: true,  // we are opening it!
			width: appinfo.wWidth+"px",
			height: appinfo.wHeight+"px",
			title: appinfo.appTitle,
			modal: false,  // no window is fixed
			content: 'index.php?'+evvtURLp+'&vtappaction=getContent&class='+classname+'&appid='+appid,
			//activate:,
			deactivate: onWindowDeactivate,
			open: onWindowOpen,
			close: onWindowClose,
			resize: onWindowResize,
			//dragend:,
			actions: arrAction
		 });
	} else {  // we put it on top
		appwindow=$('#'+windowname).data("kendoWindow");
		appwindow.toFront();
	}
}

// Eliminate window div onClose so we can open it again later
function onWindowDeactivate() {
	$('#vtapp'+this.options.vtappid).remove();
}

// Informe object that it has been resized
function onWindowResize() {
	var jskWindow = $('#vtapp'+this.options.vtappid).data("kendoWindow").wrapper;
	var jskWidth = jskWindow.css('width');
	var jskHeight = jskWindow.css('height');
	this.refresh('index.php?'+evvtURLp+'&vtappaction=doResize&class='+this.options.vtclassname+'&appid='+this.options.vtappid+'&appwidth='+jskWidth+'&appheight='+jskHeight);
}

//Inform object that it is being opened
function onWindowOpen() {
	$.ajax({
	  type: 'POST',
	  url: 'index.php',
	  data: evvtURLp+'&vtappaction=doShow&class='+this.options.vtclassname+'&appid='+this.options.vtappid
	});
}

//Inform object that it has been closed
function onWindowClose() {
	$.ajax({
	  type: 'POST',
	  url: 'index.php',
	  data: evvtURLp+'&vtappaction=doHide&class='+this.options.vtclassname+'&appid='+this.options.vtappid
	});
}

// Leaving vtApps, we have to save this users settings
function unloadCanvas(eventObject) {
	// FIXME
}

function dumpProps(obj, parent) {
   // Go through all the properties of the passed-in object
   for (var i in obj) {
      // if a parent (2nd parameter) was passed in, then use that to
      // build the message. Message includes i (the object's property name)
      // then the object's property value on a new line
      if (parent) {
    	  var msg = parent + "." + i + "\n" + obj[i];
      } else {
    	  var msg = i + "\n" + obj[i];
      }
      // Display the message. If the user clicks "OK", then continue. If they
      // click "CANCEL" then quit this level of recursion
      if (!confirm(msg)) { return; }
      // If this property (i) is an object, then recursively process the object
      if (typeof obj[i] == "object") {
         if (parent) {
        	 dumpProps(obj[i], parent + "." + i);
         } else {
        	 dumpProps(obj[i], i);
         }
      }
   }
}
