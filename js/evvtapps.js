
var evvtCurrentAppOnScreen = 0;

var vtApps = (function($) {
    
    // This is the main object that handles everything
    function VtApps() {
      this.init();
    }
    VtApps.prototype = {
      launchers: null,
      canvas: null,
      // Init app launchers
      init: function() {
        this.launchers = [];
        this.ajaxRequest('VTAPP_getLaunchers', null, this.__init, this);
      },
      // Get data from server, then create and show launchers
      __init: function(data) {
        $(document).ready($.proxy(function() {
            this.canvas = $('#evvtCanvas');
            $("#launchers").sortable({
              helper : 'clone',
              update: function(e, ui) {
            	  serial = $('#launchers').sortable('toArray');
            	  VtApps.prototype.ajaxRequest('VTAPP_doReorderApps', {'vtapp_order':serial});
              },
              placeholder: "evvtappbox evvtappbox-highlight"
            });
            $("#launchers").disableSelection();
            for(var i=0; i<data.length; i++) {
              var appLauncher = new this.VtAppLauncher(data[i]);
              this.launchers.push(appLauncher);
              if (evvtcanvas == 'windows') {
              appLauncher.show();
              }
            }
        }, this));
      },
      // Function to do ajax requests with callback function and context
      ajaxRequest: function(action, options, fn, context) {
        var data = {
          module: 'evvtApps',
          action: 'evvtAppsAjax',
          file: 'ajax',
          evvtapps_action: action
        };
        $.extend(data, options);
        $.getJSON('index.php', data, $.proxy(fn, context));
      }
    }
    
    // This class is for app launchers
    VtAppLauncher = VtApps.prototype.VtAppLauncher = function(data) {
      this.init(data);
    };
    VtAppLauncher.prototype = {
      id: null,
      name: null,
      path: null,
      key: null,
      iconPath: null,
      shortDescription: null,
      longDescription: null,
      editable: null,
      resizable: null,
      clonable: null,
      visible: null,
      canhide: null,
      canshow: null,
      background: null,
      translations: null,
      handlers: null,
      instances: null,
      // Init app launcher from data provided
      init: function(data) {
        this.id = data.id;
        this.name = data.name;
        this.path = data.path;
        this.key = data.key;
        this.iconPath = data.iconPath;
        this.shortDescription = data.shortDescription;
        this.longDescription = data.longDescription;
        this.editable = data.editable;
        this.resizable = data.resizable;
        this.clonable = data.clonable;
        this.visible = data.visible;
        this.canhide = data.canhide;
        this.canshow = data.canshow;
        this.background = data.background;
        this.translations = data.translations;
        if (data.jsFiles.length>0) {
          for(var i=0; i<data.jsFiles.length; i++) {
            $.getScript(data.jsFiles[i]);
          }
        }
        if (data.cssFiles.length>0) {
          for(var i=0; i<data.cssFiles.length; i++) {
            $('head').append('<link href="'+data.cssFiles[i]+'" type="text/css" rel="stylesheet" />');
          }
        }
        if (data.handlers!='') {
          this.handlers = eval('('+data.handlers+')');
        }
        this.instances = [];
        for(var i=0; i<data.appInstances.length; i++) {
          this.createInstance(data.appInstances[i]);
        }
      },
      // Show launcher
      show: function() {
        $('<img />')
        .attr('src', this.iconPath)
        .appendTo(
          $('<li />')
          .attr(
            {
              'id': 'vtapp-launcher-'+this.id,
              'class': 'evvtappbox',
              'original-title': this.shortDescription
            })
          .click($.proxy(this.click, this))
          .appendTo($('#launchers'))
          );
        $('#vtapp-launcher-'+this.id).tipsy();
      },
      // Handle click on launcher icon
      click: function(e) {
        if (this.canshow) {
          if ((this.clonable && e.ctrlKey) || this.instances.length==0) {
            this.newInstance();
          }
          else {
            var show = true;
            for(var i=0; i<this.instances.length; i++) {
              if (this.instances[i].isOnScreen()) {
                this.instances[i].hide();
                show = false;
              }
            }
            if (show) {
              for(var i=0; i<this.instances.length; i++) {
            	if (this.instances[i].onLaunch()) {
                this.instances[i].show();
            	}
              }
            }
          }
        }
      },
      // Request new app instance
      newInstance: function() {
        vtApps.ajaxRequest('VTAPP_createAppInstance', { launcherid: this.id }, function (data) { var inst = this.createInstance(data); this.newInstanceLaunch(inst); }, this);
      },
      // Create new app instance
      createInstance: function(data) {
        var appInstance = new vtApps.VtAppWindow(this, data);
        this.instances.push(appInstance);
        return appInstance;
      },
      removeInstance: function(instance) {
    	if (instance.onDestroy()) {
        this.instances.splice(this.instances.indexOf(instance), 1);
        instance.destroy();
        vtApps.ajaxRequest('VTAPP_removeAppInstance', { appid: instance.id }, function() {}, this);
    	}
      },
      newInstanceLaunch: function(instance) {
    	  if (instance.onLaunch()) {
    		  instance.show();
    	  } else {
   	        this.instances.splice(this.instances.indexOf(instance), 1);
   	        instance.destroy();
   	        vtApps.ajaxRequest('VTAPP_removeAppInstance', { appid: instance.id }, function() {}, this);
    	  }
      },
      translate: function(str) {
        return this.translations[str];
      },
      getWidget: function() {
        return $('#vtapp-launcher-'+this.id);
      }
    };
    
    // Class for app instances
    VtAppWindow = VtApps.prototype.VtAppWindow = function(launcher, data) {
      this.init(launcher, data);
    }
    VtAppWindow.prototype = {
      launcher: null,
      id: null,
      top: null,
      left: null,
      width: null,
      height: null,
      windowId: null,
      onscreen: false,
      resizeTimeout: false,
      // Init app instance from data provided
      init: function(launcher, data) {
        this.launcher = launcher;
        this.id = data.id;
        this.top = data.top;
        this.left = data.left;
        this.width = data.width;
        this.height = data.height;
        this.onscreen = (data.onscreen==1 ? true : false);
        if (launcher.handlers!=null) {
          $.extend(this, launcher.handlers);
        }
    	  switch (evvtcanvas) {
    	  case 'allapps':
    		  setTimeout($.proxy(this.onLoad, this), 0);
    		  break;
    	  case 'windows':
    		  this.createWindow();
    		  break
    	  case 'dashboard':
    		  break
    	  }
      },
      // Show app window
      show: function() {
        this.refresh();
  	  switch (evvtcanvas) {
	  case 'allapps':
		  toolbar = '';
          if (this.launcher.clonable && this.launcher.canhide) {
        	  toolbar += '<span id="evvtappTitleBarToolsDestroy"></span>';
          }
          if (this.launcher.clonable) {
	       	toolbar += '<span id="evvtappTitleBarToolsClone"></span>';
	      }
          toolbar += '<span id="evvtappTitleBarToolsRefresh"></span>';
	      if (this.launcher.editable) {
	       	toolbar += '<span id="evvtappTitleBarToolsEdit"></span>';
	      }
		  $('#evvtappTitleBarTools').html(toolbar);
          if (this.launcher.clonable && this.launcher.canhide) {
        	  $('#evvtappTitleBarToolsDestroy').click($.proxy(function() { this.launcher.removeInstance(this); return false; }, this));
          }
          if (this.launcher.clonable) {
          	  $('#evvtappTitleBarToolsClone').click($.proxy(function() { this.launcher.newInstance(); return false; }, this));
	      }
          $('#evvtappTitleBarToolsRefresh').click($.proxy(function() { this.refresh(); return false; }, this));
	      if (this.launcher.editable) {
	       	$('#evvtappTitleBarToolsEdit').click($.proxy(this.onEdit, this));
	      }
		  break;
	  case 'windows':
        $('#'+this.windowId).data('kendoWindow').open();
        kWin = $('#'+this.windowId).data("kendoWindow");
        kWin.toFront();
        this.onscreen = true;
        this.ajaxRequest('windowOnScreen', [ 1 ]);  // save state change of window
		  break
	  case 'dashboard':
		  break
	  }
      },
      // Hide app window
      hide: function() {
        $('#'+this.windowId).data('kendoWindow').close();
        this.onscreen = false;
        this.ajaxRequest('windowOnScreen', [ 0 ]);  // save state change of window
      },
      destroy: function() {
       switch (evvtcanvas) {
       case 'allapps':
    	   move2NextApp(0);
  		  break;
  	   case 'windows':
        $('#'+this.windowId).data('kendoWindow').destroy();
        $(document).unbind('vta.'+this.id);
		  break
	   case 'dashboard':
		  break
	   }
      },
      // Get onscreen state
      isOnScreen: function() {
        return this.onscreen;
      },
      // Create app window
      createWindow: function() {
        this.windowId = 'vtapp-window-'+this.id;
        vtApps.canvas.append('<div id="'+this.windowId+'" class="k-content"></div>');
        var actions = [];
        if (this.launcher.editable) {
          actions.push('Edit');
        }
        actions.push('Refresh');
        if (this.launcher.canhide) {
        actions.push('Minimize');
        }
        if (this.launcher.resizable) {
          actions.push('Maximize');
        }
        if (this.launcher.clonable && this.launcher.canhide) {
          actions.push('Close');
        }
        $('#'+this.windowId).kendoWindow({
            modal: false,  // no window is fixed
            visible: false,
            width: this.width,
            height: this.height,
            actions: actions,
            draggable: true,  // all windows are draggable
            resizable: this.launcher.resizable,
            dragend: $.proxy(this.moveWindow, this),
            resize: $.proxy(this.resizeWindow, this)
        });
        if (this.launcher.canshow) {
          // Get kendoWindow object
          var kWin = $('#'+this.windowId).data("kendoWindow");
          // Bind minimize button
          kWin.wrapper.find('.k-minimize').click($.proxy(function() { this.hide(); return false; }, this));
          // Bind close button
          kWin.wrapper.find('.k-close').click($.proxy(function() { this.launcher.removeInstance(this); return false; }, this));
          // Bind refresh button
          kWin.wrapper.find('.k-refresh').click($.proxy(function() { this.refresh(); return false; }, this));
          // Bind edit button
          if (this.launcher.editable) {
            kWin.wrapper.find('.k-edit').click($.proxy(this.onEdit, this));
          }
          // Position it
          if (this.top==null && this.left==null) {
            kWin.center();
          }
          else {
            kWin.wrapper.css('top', this.top);
            kWin.wrapper.css('left', this.left);
          }
        }
        if (this.launcher.visible || this.onscreen) {
        	this.show();
        }
        setTimeout($.proxy(this.onLoad, this), 0);
      },
      // Set window title
      setTitle: function(title) {
    	  switch (evvtcanvas) {
    	  case 'allapps':
    		  $('#evvtappTitleBarTitle').html(title);
    		  break;
    	  case 'windows':
        var kWin = $('#'+this.windowId).data("kendoWindow");
        kWin.title(title);
		  break
    	  case 'dashboard':
    		  break
    	  }
      },
      // Set window content
      setContent: function(content) {
    	  var id = this.id;
    	  switch (evvtcanvas) {
    	  case 'allapps':
    		  $('#evvtappContentDiv').html(content);
    		  $('#evvtappContentDiv *').each(function(index) {
    	            if ($(this).attr('id')) {
    	                $(this).attr('id', 'vtapp-id-'+id+'-'+$(this).attr('id'));
    	              }
    	          });
    		  break;
    	  case 'windows':
        var kWin = $('#'+this.windowId).data("kendoWindow");
        kWin.content(content);
        $('#'+this.windowId+' *').each(function(index) {
            if ($(this).attr('id')) {
              $(this).attr('id', 'vtapp-id-'+id+'-'+$(this).attr('id'));
            }
        });
		  break
    	  case 'dashboard':
    		  break
    	  }
      },
      safeId: function(id) {
        return 'vtapp-id-'+this.id+'-'+id;
      },
      getServerMethodURL: function(action, parameters) {
        var data = {
          module: 'evvtApps',
          action: 'evvtAppsAjax',
          file: 'ajax',
          evvtapps_action: action
        };
        $.extend(data, parameters);
        return $.param(data);
      },
      // Move app window
      moveWindow: function() {
        var kWin = $('#'+this.windowId).data("kendoWindow");
        this.top = kWin.wrapper.css('top');
        this.left = kWin.wrapper.css('left');
        this.ajaxRequest('moveWindow', [ this.top, this.left ]);
      },
      // Resize app window
      resizeWindow: function() {
        if (this.resizeTimeout!==false) {
          clearTimeout(this.resizeTimeout);
        }
        this.resizeTimeout = setTimeout($.proxy(function() {
            var kWin = $('#'+this.windowId).data("kendoWindow");
            this.width = kWin.wrapper.css('width');
            this.height = kWin.wrapper.css('height');
            this.ajaxRequest('resizeWindow', [ this.width, this.height ], $.proxy(this.onResize, this));
        }, this), 1000);
      },
      // Redraw window contents
      redrawContent: function(data) {
        this.setTitle(data.title);
        this.setContent(data.content);
        this.onRefresh();
      },
      // Refresh app window
      refresh: function() {
        this.ajaxRequest('VTAPP_getContent', null, this.redrawContent, this);
      },
      // Add listener for messages, key is in the form: com.mycompany.myapp.hello
      addListener: function(key, listener) {
        var customEventName = 'vta_'+key.replace(/\./g, '#')+'.vtapp-'+this.id;
        $(document).bind(customEventName, $.proxy(function(event, key, data) { listener.call(this, key, data); }, this));
      },
      // Remove listener for messages
      removeListener: function(key) {
        var customEventName = 'vta_'+key.replace(/\./g, '#')+'.vtapp-'+this.id;
        $(document).unbind(customEventName);
      },
      // Send message with custom data
      sendMessage: function(messageKey, data) {
        var key = this.launcher.key+'.'+messageKey;
        var customEventName = 'vta_'+key.replace(/\./g, '#');
        $(document).trigger(customEventName, [ key, data ]);
        customEventName = 'vta_'+this.launcher.key.replace(/\./g, '#');
        $(document).trigger(customEventName, [ key, data ]);
      },
      // Call the server object using ajax
      ajaxRequest: function(action, parameters, fn, context) {
        var data = {
          evvtapps_appid: this.id
        };
        if ($.isArray(parameters)) {
          for(var i=0; i<parameters.length; i++) {
            data['evvtapps_param_'+i] = parameters[i];
          }
        }
        if (context==undefined) {
          context = this;
        }
        vtApps.ajaxRequest(action, data, fn, context);
      },
      // Get query object for selector
      get: function(selector) {
        if (/^#/.test(selector)) {
          var domId = 'vtapp-id-'+this.id+'-'+selector.substr(1);
          selector = '#'+domId;
        }
        if (evvtcanvas == 'windows')
        return $(selector, $('#'+this.windowId));
        else
        return $(selector);
      },
      // Get translation
      translate: function(str) {
        return this.launcher.translate(str);
      },
      getPath: function(path) {
        if (/^\//.test(path)) {
          path = path.substr(1);
        }
        return this.launcher.path+path;
      },
      // Called when the canvas icon is clicked on, if false is returned, the default action will not be taken
      onLaunch: function() { return true; },
      // Called when the launcher is shown
      onLoad: function() {},
      // Called after a window refresh
      onRefresh: function() {},
      // Called when the close button is clicked
      onDestroy: function() {return confirm(this.translate('ReallyDestroy'));},
      // Called when the edit button is clicked
      onEdit: function() {},
      // Called when the window is resized
      onResize: function() {}
    }
    
    return new VtApps();
})(jQuery);

/** Other auxiliar functions */

function setCanvas2Window() {
	if (evvtcanvas == 'windows') {
		jQuery("#evvtCanvas").html('<ul id="launchers"></ul>');
        for(var launch=0; launch<vtApps.launchers.length; launch++) {
           	vtApps.launchers[launch].show();
           	for(var inst=0; inst<vtApps.launchers[launch].instances.length; inst++) {
           		if (vtApps.launchers[launch].instances[inst].isOnScreen()) {
           			$('#'+vtApps.launchers[launch].instances[inst].windowId).data('kendoWindow').open();
           		}
           	}
        }
	}
	return false;
}

function evvtFindConfigApp() {
	found = false;
	i = 0;
    while (i<vtApps.launchers.length && !found) {
       	found = (vtApps.launchers[i].key == 'com.tsolucio.Configuration');
       	if (found) return i;
       	i++;
    }
    return -1;
}

function evvtShowAboutUs() {
	$('#evvtAppsAboutUs').kendoWindow({
		modal: true,
		visible: true,
		width: '420px',
		actions: ["Close"],
		height: '130px',
		draggable: true,
		resizable: false
	});
	var kendoWindow = $("#evvtAppsAboutUs").data("kendoWindow");
	kendoWindow.content('<table style="width: 100%;"><tbody><tr><td><b>vtEvolutivo::vtApps</b><br/>Copyright &copy; 2012<br/><br/>'+
			'<b>vtApps</b> is an <b><a href="http://www.evolutivo.it">Evolutivo Initiative</a></b><br/></td><td><img src="modules/evvtApps/images/vtApps.png"></td></tr>'+
			'<tr><td colspan="2">Which means it is a joint venture project of the companies:</td></tr>'+
			'<tr><td colspan="2"><b>OpenCubed. JPL TSolucio, S.L. and StudioSynthesis, S.R.L.</b></td></tr></tbody></table>');
	kendoWindow.title('About us');
	kendoWindow.center();
}

function evvtHeaderToggle() {
	$('#evvtheader').toggle();
	if ($('#evvtheader').css('display')=='none') {
		$('#evvtheaderhideimage').attr('src','modules/evvtApps/images/showpanel.png');
	} else {
		$('#evvtheaderhideimage').attr('src','modules/evvtApps/images/hidepanel.png');
	}
}

function hideAllInstances() {
	for(var launch=0; launch<vtApps.launchers.length; launch++) {
       	for(var inst=0; inst<vtApps.launchers[launch].instances.length; inst++) {
       		if (vtApps.launchers[launch].instances[inst].isOnScreen()) {
       			$('#'+vtApps.launchers[launch].instances[inst].windowId).data('kendoWindow').close();
       		}
       	}
	}
}

function makeContent(contentName){
	jQuery('#evvtheaderCenter a').removeClass('evvtheaderCenterActive');
	evvtcanvas = contentName;
	switch (contentName) {
	  case 'windows':
		evvtAllAppsDataReceived({'icon':'modules/evvtApps/images/blank.png', 'description':''});
		jQuery("#evvtHeaderJumpTo").hide();
        jQuery("#evvtleftButton").hide();
        jQuery("#evvtrightButton").hide();
        jQuery("#evvtCanvas").css('width','96%');
        jQuery("#evvthcwin").addClass('evvtheaderCenterActive');
		setCanvas2Window();
		break;
	  case 'dashboard':
		evvtAllAppsDataReceived({'icon':'modules/evvtApps/images/blank.png', 'description':''});
		jQuery("#evvtHeaderJumpTo").hide();
		hideAllInstances();
        jQuery("#evvtleftButton").hide();
        jQuery("#evvtrightButton").hide();
        jQuery("#evvtCanvas").css('width','96%');
        jQuery("#evvthcdsh").addClass('evvtheaderCenterActive');
        jQuery("#evvtCanvas").html("dashboard");
		break;
	  case 'allapps':
		hideAllInstances();
		jQuery("#evvtHeaderJumpTo").show();
        jQuery("#evvtleftButton").show();
        jQuery("#evvtrightButton").show();
        jQuery("#evvtCanvas").css('width','90%');
        jQuery("#evvthcapp").addClass('evvtheaderCenterActive');
        move2App(evvtCurrentAppOnScreen);
		break;
	}
    return false;
};

function move2NextApp(offset){
	if (evvtcanvas == 'allapps') {
        numinstances=0;
        instancesid = [];
		for(var launch=0; launch<vtApps.launchers.length; launch++) {
           	for(var inst=0; inst<vtApps.launchers[launch].instances.length; inst++) {
           		instancesid.push(vtApps.launchers[launch].instances[inst]);
           		numinstances++;
           	}
        }
		newpos = evvtCurrentAppOnScreen + offset;
		evvtCurrentAppOnScreen = Math.abs(newpos % numinstances);
		if (newpos < 0) {
			evvtCurrentAppOnScreen = numinstances - evvtCurrentAppOnScreen;
		}
		//configapp = evvtFindConfigApp();
		//vtApps.launchers[configapp].instances[0].ajaxRequest('getAppUserData', [ instancesid[evvtCurrentAppOnScreen].launcher.id ], $.proxy(evvtAllAppsDataReceived));
		evvtAllAppsDataReceived({'icon': instancesid[evvtCurrentAppOnScreen].launcher.iconPath, 'description':instancesid[evvtCurrentAppOnScreen].launcher.shortDescription});
		jQuery("#evvtCanvas").html('<div id="evvtappTitleBar"><div id="evvtappTitleBarTitle"></div><div id="evvtappTitleBarTools"></div></div><div id="evvtappContentDiv"></div>');
		instancesid[evvtCurrentAppOnScreen].show();
	}	
    return false;
};

function move2App(posicion){
	if (evvtcanvas == 'allapps') {
        numinstances=0;
        instancesid = [];
		for(var launch=0; launch<vtApps.launchers.length; launch++) {
           	for(var inst=0; inst<vtApps.launchers[launch].instances.length; inst++) {
           		instancesid.push(vtApps.launchers[launch].instances[inst]);
           		numinstances++;
           	}
        }
		if (posicion < 0 || posicion > numinstances) {
			posicion = 0;  // invalid position, we set on the first one
		}
		evvtCurrentAppOnScreen = posicion;
		//configapp = evvtFindConfigApp();
		//vtApps.launchers[configapp].instances[0].ajaxRequest('getAppUserData', [ instancesid[evvtCurrentAppOnScreen].launcher.id ], $.proxy(evvtAllAppsDataReceived));
		evvtAllAppsDataReceived({'icon': instancesid[evvtCurrentAppOnScreen].launcher.iconPath, 'description':instancesid[evvtCurrentAppOnScreen].launcher.shortDescription});
		jQuery("#evvtCanvas").html('<div id="evvtappTitleBar"><div id="evvtappTitleBarTitle"></div><div id="evvtappTitleBarTools"></div></div><div id="evvtappContentDiv"></div>');
		instancesid[evvtCurrentAppOnScreen].show();
	}	
    return false;
};

function jumpToMenu() {
	$("#vtappStatus").show();
	var DDapplistTimer; 
	var datastr = '';
	var numinst = 0;
	for(var launch=0; launch<vtApps.launchers.length; launch++) {
       	for(var inst=0; inst<vtApps.launchers[launch].instances.length; inst++) {
       		datastr = datastr + '{"listid": ' + numinst + ', "icon":"' + vtApps.launchers[launch].iconPath + '", "title": "';
       		ajaxurl = vtApps.launchers[launch].instances[inst].getServerMethodURL('getTitle', {
                evvtapps_appid: vtApps.launchers[launch].instances[inst].id
            });
       		$.ajax({
       		  url: 'index.php?' + ajaxurl,
       		  async:false
       		}).done(function(apptitle) { 
       			datastr = datastr + apptitle + '"}';
       		});
       		if (inst+1<vtApps.launchers[launch].instances.length) datastr = datastr + ',';
       		numinst++;
       	}
       	if (launch+1<vtApps.launchers.length) datastr = datastr + ',';
    }
	dataobj = jQuery.parseJSON('[' + datastr + ']');
	$("#vtappDDListDiv").show();
	$("#vtappDDListInput").show();
    $("#vtappDDListInput").kendoDropDownList({
        dataTextField: "title",
        dataValueField: "listid",
        template: '<span style="vertical-align:middle;"><img src="${ data.icon }" height="16px"/>' +'&nbsp;${ data.title }</span>',
        dataSource: dataobj,
        change: function(e) {
            move2App(e.sender.selectedIndex);
            var ddl = $("#vtappDDListInput").data("kendoDropDownList");
            ddl.close();
            $("#vtappDDListDiv").hide();
        },
        open: function(e) {
        	clearTimeout(DDapplistTimer);
        },
        close: function(e) {
        	DDapplistTimer = setTimeout(function () { $("#vtappDDListDiv").hide(); }, 1200);
        }
    });
    var dropdownlist = $("#vtappDDListInput").data("kendoDropDownList");
    dropdownlist.list.width(400);
    $("#vtappStatus").hide();
    DDapplistTimer = setTimeout(function () { $("#vtappDDListDiv").hide(); }, 4000);
}

function evvtAllAppsDataReceived(data) {
    jQuery('#evvtHeaderImage').attr('src', data.icon);
    jQuery('#evvtHeaderDesc').html(data.description);
}

