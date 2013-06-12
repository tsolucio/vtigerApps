/*
 * Copyright 2012 JPL TSolucio, S.L. -- This file is a part of evvtApps.
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
 * Author: JPL TSolucio, S.L.
 */
var evvtCurrentAppOnScreen = 0;
var evvtMaxDashboardElementID = 0;
var evvtDoingDashboardPaint = 0;
var defaultcanvasimage = '';
var dashboardeditorloaded = false;

// Initializes header and canvas
$(document).ready($.proxy(function() {
	$('#defaultcanvasimg1').click($.proxy(function() { changeDefaultCanvas('windows');}));
	$('#defaultcanvasimg2').click($.proxy(function() { changeDefaultCanvas('dashboard');}));
	$('#defaultcanvasimg3').click($.proxy(function() { changeDefaultCanvas('allapps');}));
	$('#defaultcanvasimg1, #defaultcanvasimg2, #defaultcanvasimg3').hover(
		$.proxy(function(tgt) {
			tgtimg = $('#'+tgt.target.id);
			defaultcanvasimage = tgtimg.attr('src');
			tgtimg.attr('src','modules/evvtApps/images/selectedcanvas.png');
			}),
		$.proxy(function(tgt) {
			setDefaultCanvasImage();
			})
	);
}));
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
            for(var i=0; i<data.length; i++) {
              var appLauncher = new this.VtAppLauncher(data[i]);
              this.launchers.push(appLauncher);
            };
            makeContent(evvtcanvas);
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
      newInstance: function(divid) {
        vtApps.ajaxRequest('VTAPP_createAppInstance', { launcherid: this.id }, function (data) { var inst = this.createInstance(data,divid); this.newInstanceLaunch(inst); }, this);
      },
      // Create new app instance
      createInstance: function(data,divid) {
        var appInstance = new vtApps.VtAppWindow(this, data,divid);
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
    VtAppWindow = VtApps.prototype.VtAppWindow = function(launcher, data, divid) {
      this.init(launcher, data, divid);
    };
    VtAppWindow.prototype = {
      launcher: null,
      id: null,
      divid: null,
      top: null,
      left: null,
      width: null,
      height: null,
      windowId: null,
      title: '',
      onscreen: false,
      resizeTimeout: false,
      // Init app instance from data provided
      init: function(launcher, data, divid) {
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
    		  break;
    	  case 'dashboard':
    		  this.divid = divid;
    		  setTimeout($.proxy(this.onLoad, this), 0);
    		  break;
    	  }
      },
      getmydivid: function() {
    	  retval = '';
    	  if (this.divid!=null) {
    		  retval = this.divid;
    	  } else {
	    	  var treeview = $("#evvtDashboardEditorTreeview").data("kendoTreeView");
	    	  var attrid = treeview.element.find("span[splitprops*='vtappid"+'":"'+this.id+"']").attr('id');
	    	  retval = attrid.substring(attrid.indexOf('-')+1);
	    	  this.divid = retval;
    	  }
    	  return retval;
      },
      // Show app window
      show: function() {
        this.refresh();
  	  switch (evvtcanvas) {
	  case 'allapps':
		  toolbar = '';
          if (this.launcher.clonable && this.launcher.canhide) {
        	  toolbar += '<span id="evvtappTitleBarToolsDestroy" class="evvtappTitleBarToolsDestroy"></span>';
          }
          if (this.launcher.clonable) {
	       	toolbar += '<span id="evvtappTitleBarToolsClone" class="evvtappTitleBarToolsClone"></span>';
	      }
          toolbar += '<span id="evvtappTitleBarToolsRefresh" class="evvtappTitleBarToolsRefresh"></span>';
	      if (this.launcher.editable) {
	       	toolbar += '<span id="evvtappTitleBarToolsEdit" class="evvtappTitleBarToolsEdit"></span>';
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
		if (this.windowId == null) {
			this.createWindow();  // this one was created on another canvas
		}
        kWin = $('#'+this.windowId).data("kendoWindow");
        kWin.open();
        kWin.toFront();
        this.onscreen = true;
        this.ajaxRequest('windowOnScreen', [ 1 ]);  // save state change of window
		  break;
	  case 'dashboard':
		  divid = this.getmydivid();
		  toolbar = '';
          if (this.launcher.clonable && this.launcher.canhide) {
        	  toolbar += '<span id="evvtappTitleBarToolsDestroy-'+divid+'" class="evvtappTitleBarToolsDestroy"></span>';
          }
          toolbar += '<span id="evvtappTitleBarToolsRefresh-'+divid+'" class="evvtappTitleBarToolsRefresh"></span>';
	      if (this.launcher.editable) {
	       	toolbar += '<span id="evvtappTitleBarToolsEdit-'+divid+'" class="evvtappTitleBarToolsEdit"></span>';
	      }
		  $('#evvtappTitleBarTools-'+divid).html(toolbar);
          if (this.launcher.clonable && this.launcher.canhide) {
        	  $('#evvtappTitleBarToolsDestroy-'+divid).click($.proxy(function() { this.launcher.removeInstance(this); return false; }, this));
          }
          $('#evvtappTitleBarToolsRefresh-'+divid).click($.proxy(function() { this.refresh(); return false; }, this));
	      if (this.launcher.editable) {
	       	$('#evvtappTitleBarToolsEdit-'+divid).click($.proxy(this.onEdit, this));
	      }
		  break;
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
		  break;
	   case 'dashboard':
		var treeview = $("#evvtDashboardEditorTreeview").data("kendoTreeView");
		var atributos = treeview.element.find("span[splitprops*='vtappid"+'":"'+this.id+"']");
		var treenode = atributos.closest('li');
		var attrid = atributos.attr('id');
		var divid = attrid.substring(attrid.indexOf('-')+1);
		activateSaveRefreshButton();
		var splitprops = jQuery.parseJSON(atributos.attr("splitprops"));
       	splitprops.vtappid = 0;
        sprops = JSON.stringify(splitprops);
        atributos.attr("splitprops",sprops);
    	$('#evvtappContentDiv-' + divid).html('');
    	$('#evvtappTitleBarTitle-' + divid).html('');
    	$('#evvtappTitleBarTools-' + divid).html('');
    	newtext = vtApps.launchers[0].translate('vtApp Container');
    	newimage = 'evvtdbcol';
        changeTreenodeContent(treenode,newtext,newimage);
        $("#evvtSplitvtAppid").data("kendoDropDownList").dataSource.data(getAppDropdownData());
		  break;
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
          kWin.wrapper.find('.k-i-minimize').click($.proxy(function() { this.hide(); return false; }, this));
          // Bind close button
          kWin.wrapper.find('.k-i-close').click($.proxy(function() { this.launcher.removeInstance(this); return false; }, this));
          // Bind refresh button
          kWin.wrapper.find('.k-i-refresh').click($.proxy(function() { this.refresh(); return false; }, this));
          // Bind edit button
          if (this.launcher.editable) {
            kWin.wrapper.find('.k-i-edit').click($.proxy(this.onEdit, this));
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
    	  this.title = title;
    	  switch (evvtcanvas) {
    	  case 'allapps':
    		  $('#evvtappTitleBarTitle').html(title);
    		  break;
    	  case 'windows':
        var kWin = $('#'+this.windowId).data("kendoWindow");
        kWin.title(title);
		  break;
    	  case 'dashboard':
    		  divid = this.getmydivid();
    		  $('#evvtappTitleBarTitle-'+divid).html(title);
    		  break;
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
		  break;
    	  case 'dashboard':
    		  divid = this.getmydivid();
    		  $('#evvtappContentDiv-'+divid).html(content);
    		  $('#evvtappContentDiv-'+divid+' *').each(function(index) {
    	            if ($(this).attr('id')) {
    	                $(this).attr('id', 'vtapp-id-'+id+'-'+$(this).attr('id'));
    	              }
    	          });
    		  break;
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
    };
    
    return new VtApps();
})(jQuery);

/** Other auxiliar functions */

function setCanvas2Window() {
	if (evvtcanvas == 'windows') {
		jQuery("#evvtDashboardCanvas").hide();
		jQuery("#evvtCanvas").html('<ul id="launchers"></ul>').show();
        $("#launchers").sortable({
            helper : 'clone',
            update: function(e, ui) {
          	  serial = $('#launchers').sortable('toArray');
          	  vtApps.ajaxRequest('VTAPP_doReorderApps', {'vtapp_order':serial});
            },
            placeholder: "evvtappbox evvtappbox-highlight"
          });
        $("#launchers").disableSelection();
        evvtapps_gotoappnum = -1;
        for(var launch=0; launch<vtApps.launchers.length; launch++) {
           	vtApps.launchers[launch].show();
           	for(var inst=0; inst<vtApps.launchers[launch].instances.length; inst++) {
           		if (vtApps.launchers[launch].key==evvtapps_gotoapp) {
           			evvtapps_gotoappnum = launch;
           		}
           		if (vtApps.launchers[launch].instances[inst].isOnScreen() || vtApps.launchers[launch].key==evvtapps_gotoapp) {
           			if (vtApps.launchers[launch].instances[inst].windowId == null) {
           				vtApps.launchers[launch].instances[inst].createWindow();  // this one was created on another canvas
           			} else {
           				$('#'+vtApps.launchers[launch].instances[inst].windowId).data('kendoWindow').open();
           			}
           		}
           	}
        }
        if (evvtapps_gotoappnum>-1) {
        	vtApps.launchers[evvtapps_gotoappnum].instances[0].show();
        }
	}
	return false;
}

function setCanvas2Dashboard() {
	if (evvtcanvas == 'dashboard') {
   		ajaxurl = 'index.php?module=evvtApps&action=evvtAppsAjax&file=ajax&evvtapps_action=VTAPP_getDashboardLayout';
   		$.ajax({
     		  url: ajaxurl
     		}).done(function(dblayout) { 
     			setCanvas2DashboardWithData(jQuery.parseJSON(dblayout));
     		});
	}
}

function setCanvas2DashboardWithData(dblayout) {
	evvtDoingDashboardPaint = true;
		if (jQuery.isPlainObject(dblayout)) {
			dblayout = [{
				id: 1,
				splitprops: '{"vtappid":0, "evvttype": "group", "status":0, "splitCollapsed":false, "splitCollapsable":false, "splitMax":"100%", "splitMin":300, "splitResize":true, "splitSize":"80%", "splitScroll":true}',
				text: vtApps.launchers[0].translate('Row Layout'),
				expanded: true,
				spriteCssClass: "evvtdbrow",
				items : []
			}];
		}
	    $("#evvtDashboardEditorTreeview").kendoTreeView({
        template: kendo.template($("#evvtDashboardEditorTreeview-template").html()),
        dragAndDrop: false,
        dataSource: dblayout,
        select: function (e) {
        	selectTreeElement(e.node);
        },
        dragend: function (e) {
        	activateSaveRefreshButton();
        },
        collapse: function (e) {
        	$('#evvtDBETCollapseExpand').removeClass('evvtDashboardEditorToolActive').addClass('evvtDashboardEditorToolUnactive');
        	$('#evvtDashboardEditorPropview').hide();
        },
        expand: function (e) {
        	$('#evvtDBETCollapseExpand').removeClass('evvtDashboardEditorToolUnactive').addClass('evvtDashboardEditorToolActive');
        	$('#evvtDashboardEditorPropview').hide();
        }
	    });
	    var treeview = $("#evvtDashboardEditorTreeview").data("kendoTreeView");
	    evvtMaxDashboardElementID = getMaxElementsIDFromTree();
        // Delete button behavior
        $(document).on("click", ".delete-link", function() {
        	var selectedNode = treeview.select();
        	if (getdbproperty(selectedNode,'evvttype')=='group') {
        		msg = 'ReallyDestroyGroup';
        	} else {
        		msg = 'ReallyDestroy';
        	}
            vrfy_delt = confirm(vtApps.launchers[0].translate(msg));
            if(vrfy_delt) {
            	$('#evvtdbhighlightpane').remove();
            	recursiveDeleteNode(treeview, selectedNode);
	            activateSaveRefreshButton();
            }
        });
        $(document).on("click", "#evvtDBETAddRowLayout", function() {
            var selectedNode = treeview.select();
            if (selectedNode.length == 1) {
            	evvtMaxDashboardElementID++;
            	if (getdbproperty(selectedNode,'evvttype')=='group') {
            		newelem = getNewGroupElement();
            	} else {
            		newelem = getNewItemElement();
            	}
	            treeview.insertAfter(newelem, selectedNode);
	            activateSaveRefreshButton();
            }
        });
        $(document).on("click", "#evvtDBETAddvtAppCell", function() {
            var selectedNode = treeview.select();
            if (selectedNode.length == 1) {
            	evvtMaxDashboardElementID++;
            	if (getdbproperty(selectedNode,'evvttype')=='group') {
            		newelem = getNewItemElement();
            	} else {
            		newelem = getNewGroupElement();
            	}
            	treeview.append(newelem, selectedNode);
            	activateSaveRefreshButton();
            }
        });
        $(document).on("click", "#evvtDBETCollapseExpand", function() {
            if ($('#evvtDBETCollapseExpand').hasClass('evvtDashboardEditorToolActive')) {
            	$('#evvtDBETCollapseExpand').removeClass('evvtDashboardEditorToolActive').addClass('evvtDashboardEditorToolUnactive');
            	treeview.collapse(".k-item");
            } else {
            	$('#evvtDBETCollapseExpand').removeClass('evvtDashboardEditorToolUnactive').addClass('evvtDashboardEditorToolActive');
            	treeview.expand(".k-item");
            }
        });
        $(document).on("click", "#evvtDBETRefresh", function() {
        	$("#vtappStatus").show();
        	paintLayoutOnCanvas();
        	$('#evvtDBETRefresh').removeClass('evvtDashboardEditorToolActive').addClass('evvtDashboardEditorToolUnactive');
        	$("#vtappStatus").hide();
        });
        $(document).on("click", "#evvtDBETSave", function() {
        	$("#vtappStatus").show();
        	evvtjsontree = treeToJson(treeview);
       		ajaxurl = 'index.php?module=evvtApps&action=evvtAppsAjax&file=ajax&evvtapps_action=VTAPP_setDashboardLayout';
       		$.ajax({
         		  url: ajaxurl,
         		  type: "POST",
         		  data: 'evvtdblayout=' + JSON.stringify(evvtjsontree)
         		}).done(function() {
                    $('#evvtDBETSave').removeClass('evvtDashboardEditorToolActive').addClass('evvtDashboardEditorToolUnactive');
                    $("#vtappStatus").hide();
         		});
       		paintLayoutOnCanvas();
        });
        numfldops = {
        		   format: "0",
        		   min: 300,
        		   max: 2000,
        		   step: 10
        		};
        $("#evvtSplitSize").width('60px').kendoNumericTextBox($.extend(numfldops, {
        	change: function() { doSizeChange(this.value()); },
        	spin: function() { doSizeChange(this.value()); }
        }));
        $("#evvtSplitMax").width('60px').kendoNumericTextBox($.extend(numfldops, {
        	change: function() { doMaxMinChange(this.value(),true); },
        	spin: function() { doMaxMinChange(this.value(),true); }
        }));
        $("#evvtSplitMin").width('60px').kendoNumericTextBox($.extend(numfldops, {
        	change: function() { doMaxMinChange(this.value(),false); },
        	spin: function() { doMaxMinChange(this.value(),false); }
        }));
        assignAppDropdown();
        $('#evvtDashboardEditorPropview').hide();
        // now the content panes
        if (!dashboardeditorloaded) {
        $("#evvtDashboardEditor").kendoSplitter({
            orientation: "vertical",
            panes: [
                { collapsible: false, resizable: true, size: '364px' },
                { collapsible: false, resizable: true, size: '200px' }
            ]
        });
        $('#evvtDashboardEditorWindow').kendoWindow({
            width: '300px',
            height: '600px',
            title: vtApps.launchers[0].translate('DashboardEditor'),
            close: function() {
              if (!$('#evvtDashboardEditorWindow').data('closedByButton')) {
                $('#evvtDashboardEditorWindow').data('visibility', false);
              }
              $('#evvtDashboardEditorWindow').data('closedByButton', false);
              $('div[evvttype="item"]').unbind('click');
              $('#evvtdbhighlightpane').remove();
            }
        });
        $('#evvtDashboardEditorWindow').data('visibility', true);
        $('#evvtDashboardEditorWindow').data('closedByButton', false);
        $('#evvtDashboardEditorWindow').data('kendoWindow').center();
        toggleDashboardEditor();
        dashboardeditorloaded = true;
        }
        // now the layout
        paintLayoutOnCanvas();
        selectTreeElement(treeview.element.find('.k-group').children('li').first(),true);
        $('#evvtdbhighlightpane').remove();
        evvtDoingDashboardPaint = false;
}

function paintLayoutOnCanvas() {
	evvtDoingDashboardPaint = true;
	var treeview = $("#evvtDashboardEditorTreeview").data("kendoTreeView");
	evvtdblayout = treeToLayout(treeview);
	layout = '';
	for ( var it = 0; it < evvtdblayout.length; it++) {
		layout = layout + evvtdblayout[it].toString();
	}
	$('#evvtDashboardLayout').html(layout);
	treeview.element.find('.k-group').each(function() {
		var ulthis = $(this);
		var istoplevel = ulthis.children('li').first().hasClass('k-first');
		var evvttype = getdbproperty(ulthis.children('li').first(),'evvttype');
		var ksplit = { orientation: (evvttype=='group' ? 'vertical' : 'horizontal') };
		var panes = [];
		ulthis.children('li').each(function() {
			atributos = getAlldbproperty($(this));
			panes.push({
				collapsible: atributos.splitCollapsable,
				collapsed: atributos.splitCollapsed,
				size: atributos.splitSize+'px',
				max: atributos.splitMax+'px',
				min: atributos.splitMin+'px',
				resizable: atributos.splitResize,
				//myvalue: $(this).find("> div span span[atributos]").attr('id').substring(14),
				scrollable: atributos.splitScroll
			});
		});
		ksplit.panes = panes;
		ksplit.layoutChange = function(p){
			doSplitterSizeChange(p.sender.element[0].id);
		};
		ksplit.collapse = function(p){
			doCollapsedChange(true,false);
		};
		ksplit.expand = function(p){
			doCollapsedChange(false,false);
		};
		var ksplitdiv =''; 
		if (istoplevel) {
			ksplitdiv = '#evvtDashboardLayout';
		} else {
			ksplitdiv = '#evvtdbappdiv-' + ulthis.closest('li').find("> div span span[atributos]").attr('id').substring(14);
		}
		$(ksplitdiv).kendoSplitter(ksplit);
	});
	/**  click event is not set because editor panel is hidden by default
	 *  this code must be activated if editor panel is open by default
	 */  
	$('div[evvttype="item"]').each(function (idx,item) {
		$(item).bind('click',function (e) {
			divappid = this.id.substring(this.id.indexOf('-')+1);
			selectTreeElement($('#evvtdbappdata-'+divappid).closest('li'),true);
		});
	});
	$(window).resize(evvttriggerResize);
	fillinpanes();
	evvtDoingDashboardPaint = false;
	return true;
}

function fillinpanes() {
	var treeview = $("#evvtDashboardEditorTreeview").data("kendoTreeView");
	for(var launch=0; launch<vtApps.launchers.length; launch++) {
       	for(var inst=0; inst<vtApps.launchers[launch].instances.length; inst++) {
       		atributos = treeview.element.find("span[splitprops*='vtappid"+'":"'+vtApps.launchers[launch].instances[inst].id+"']");
       		if (atributos.length>0) { // assigned to some pane
       			vtApps.launchers[launch].instances[inst].show();
       		}
       	}
    }
}

function selectTreeElement(enode,selnode) {
	if (selnode) {
		var treeview = $("#evvtDashboardEditorTreeview").data("kendoTreeView");
		treeview.select(enode);
	}
	$("#evvtDashboardEditorTreeview li a").removeClass("delete-link");
	if (getNumberOfElementsFromTree()>1 && !($(enode).hasClass('k-first') && $(enode).hasClass('k-last'))) {
		$(enode).find("> div span a").addClass("delete-link");
	}
	$('#evvtDBETAddRowLayout').removeClass('evvtDashboardEditorToolUnactive').addClass('evvtDashboardEditorToolActive');
	$('#evvtDBETAddvtAppCell').removeClass('evvtDashboardEditorToolUnactive').addClass('evvtDashboardEditorToolActive');
	$('#evvtDashboardEditorPropview').show();
	atributos = $(enode).find("> div span span[atributos]");
	splitprops = jQuery.parseJSON(atributos.attr("splitprops"));
	if (splitprops.evvttype=='group') {
		$('#evvtSplitvtAppidDiv').hide();
	} else {
		$('#evvtSplitvtAppidDiv').show();
	}
	divid = atributos.attr('id').substring(14);
	$('#evvtdbhighlightpane').remove();
	$('#evvtdbappdiv-'+divid).append('<div id="evvtdbhighlightpane" class="evvtdbhighlightpane"></div>');
	$('#evvtdbhighlightpane').width($('#evvtdbappdiv-'+divid).width()-5);
	$('#evvtdbhighlightpane').height($('#evvtdbappdiv-'+divid).height()-4);
	$('#evvteditingdiv').val('#evvtdbappdiv-'+divid);
	showdbproperties(atributos);
}

function activateSaveRefreshButton() {
	$('#evvtDBETSave').removeClass('evvtDashboardEditorToolUnactive').addClass('evvtDashboardEditorToolActive');
	$('#evvtDBETRefresh').removeClass('evvtDashboardEditorToolUnactive').addClass('evvtDashboardEditorToolActive');
}

function treeToJson(treeview, root) {
    root = root || treeview.element.children(".k-group");
    return root.children().map(function() {
        var result = { text: treeview.text(this).replace(/\n|\t/g,'').trim() };
        var atributos = $(this).find("> div span span[atributos]");
            result.splitprops = atributos.attr('splitprops');
            result.id = parseInt(atributos.attr('id').substring(14));
            result.expanded = ($(this).children("ul").css("display") != "none");
            result.spriteCssClass = ($(this).find('> div .k-sprite').hasClass('evvtdbrow') ? 'evvtdbrow' : ($(this).find('> div .k-sprite').hasClass('evvtdbapp') ? 'evvtdbapp' : 'evvtdbcol')); 
            items = treeToJson(treeview, $(this).children(".k-group"));
        if (items.length) {
            result.items = items;
        }
        return result;
      }).toArray();
}

function treeToLayout(treeview, root) {
    root = root || treeview.element.children(".k-group");
    return root.children().map(function() {
        var atributos = $(this).find("> div span span[atributos]");
        var splitprops = jQuery.parseJSON(atributos.attr("splitprops"));
        var divappid = atributos.attr("id").substring(14);
        var startdiv = '<div id="evvtdbappdiv-'+ divappid + '"';
        var result = '';
        if (splitprops.evvttype=='item') {
        	result = result + '<div class="evvtappTitleBar" id="evvtappTitleBar-'+divappid+'"><div class="evvtappTitleBarTitle" id="evvtappTitleBarTitle-'+divappid+'"></div><div class="evvtappTitleBarTools" id="evvtappTitleBarTools-'+divappid+'"></div></div><div class="evvtappContentDiv" id="evvtappContentDiv-'+divappid+'"></div>';
        }
        var items = treeToLayout(treeview, $(this).children(".k-group"));
        if (items.length) {
        	for ( var it = 0; it < items.length; it++) {
        		result = result + items[it].toString();
			}
        	startdiv = startdiv + ' evvttype="group">';
        } else {
        	startdiv = startdiv + ' evvttype="item">';
        }
        result = startdiv + result + '</div>';
        return result;
      });
}

function recursiveDeleteNode(treeview, root) {
    root = root || treeview.element.children(".k-group");
    return root.children().each(function() {
        recursiveDeleteNode(treeview, $(this).children(".k-group"));
        treeview.remove($(this));
        return result;
      });
}

function getNewItemElement() {
	return {
		id: evvtMaxDashboardElementID,
		splitprops: '{"vtappid":0, "evvttype": "item", "status":0, "splitCollapsed":false, "splitCollapsable":true, "splitMax":"100%", "splitMin":300, "splitResize":true, "splitSize":"100%", "splitScroll":true}',
		text: vtApps.launchers[0].translate('vtApp Container'),
		expanded: true,
		spriteCssClass: "evvtdbcol"
	};
}

function getNewGroupElement() {
	return {
		id: evvtMaxDashboardElementID,
		splitprops: '{"vtappid":0, "evvttype": "group", "status":0, "splitCollapsed":false, "splitCollapsable":true, "splitMax":"100%", "splitMin":300, "splitResize":true, "splitSize":"100%", "splitScroll":true}',
		text: vtApps.launchers[0].translate('Row Layout'),
		expanded: true,
		spriteCssClass: "evvtdbrow",
		items : []
	};
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

function evvtHeaderToggleAll() {
	if ($('#evvtheaderhideallimage').attr('src')=='modules/evvtApps/images/showallpanel.png') {
		$('#evvtheader').show();
		$('.hdrNameBg').show();
		$('.hdrTabBg').show();
		$('.level2Bg').show();
		$('#evvtheaderhideimage').attr('src','modules/evvtApps/images/hidepanel.png');
		$('#evvtheaderhideallimage').attr('src','modules/evvtApps/images/hideallpanel.png');
	} else {
		$('#evvtheader').hide();
		$('.hdrNameBg').hide();
		$('.hdrTabBg').hide();
		$('.level2Bg').hide();
		$('#evvtheaderhideimage').attr('src','modules/evvtApps/images/showpanel.png');
		$('#evvtheaderhideallimage').attr('src','modules/evvtApps/images/showallpanel.png');
	}
}

function hideAllInstances() {
	for(var launch=0; launch<vtApps.launchers.length; launch++) {
       	for(var inst=0; inst<vtApps.launchers[launch].instances.length; inst++) {
       		if (vtApps.launchers[launch].instances[inst].isOnScreen()) {
       			if (vtApps.launchers[launch].instances[inst].windowId!=null)
       			$('#'+vtApps.launchers[launch].instances[inst].windowId).data('kendoWindow').close();
       		};
       	};
	};
}

function makeContent(contentName){
	jQuery('#evvtheaderCenter a').removeClass('evvtheaderCenterActive');
	evvtcanvas = contentName;
	switch (contentName) {
	  case 'windows':
	    hideDashboardEditor();
		evvtAllAppsDataReceived({'icon':'modules/evvtApps/images/blank.png', 'description':''});
		jQuery("#evvtHeaderJumpTo").hide();
        jQuery("#evvtleftButton").hide();
        jQuery("#evvtrightButton").hide();
        jQuery("#evvtCanvas").width('96%');
        jQuery("#evvtCanvas").show();
        jQuery("#evvtDashboardCanvas").hide();
        jQuery("#evvthcwin").addClass('evvtheaderCenterActive');
		setCanvas2Window();
		break;
	  case 'dashboard':
	    showDashboardEditor();
		evvtAllAppsDataReceived({'icon':'modules/evvtApps/images/blank.png', 'description':''});
		jQuery("#evvtHeaderJumpTo").hide();
		hideAllInstances();
        jQuery("#evvtleftButton").hide();
        jQuery("#evvtrightButton").hide();
        jQuery("#evvtCanvas").hide();
        jQuery("#evvtDashboardCanvas").show();
        jQuery("#evvthcdsh").addClass('evvtheaderCenterActive');
        setCanvas2Dashboard();
		break;
	  case 'allapps':
	    hideDashboardEditor();
		hideAllInstances();
		jQuery("#evvtHeaderJumpTo").show();
        jQuery("#evvtleftButton").show();
        jQuery("#evvtrightButton").show();
        jQuery("#evvtCanvas").width('90%');
        jQuery("#evvtCanvas").height('80%');
        jQuery("#evvtCanvas").show();
        jQuery("#evvtDashboardCanvas").hide();
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
		evvtAllAppsDataReceived({'icon': instancesid[evvtCurrentAppOnScreen].launcher.iconPath, 'description':instancesid[evvtCurrentAppOnScreen].launcher.shortDescription});
		jQuery("#evvtCanvas").html('<div id="evvtappTitleBar" class="evvtappTitleBar"><div id="evvtappTitleBarTitle" class="evvtappTitleBarTitle"></div><div id="evvtappTitleBarTools" class="evvtappTitleBarTools"></div></div><div id="evvtappContentDiv" class="evvtappContentDiv"></div>');
		instancesid[evvtCurrentAppOnScreen].show();
	}	
    return false;
};

function move2App(posicion){
	if (evvtcanvas == 'allapps') {
		evvtapps_gotoappnum=-1;
        numinstances=0;
        instancesid = [];
		for(var launch=0; launch<vtApps.launchers.length; launch++) {
           	for(var inst=0; inst<vtApps.launchers[launch].instances.length; inst++) {
           		instancesid.push(vtApps.launchers[launch].instances[inst]);
        		if (evvtapps_gotoapp!='' && evvtapps_gotoapp==instancesid[numinstances].launcher.key) {
        			evvtapps_gotoappnum=numinstances;
        			evvtapps_gotoapp='';
        		}
           		numinstances++;
           	}
        }
		if (evvtapps_gotoappnum!=-1) {
			posicion=evvtapps_gotoappnum;
        }
		if (posicion < 0 || posicion > numinstances) {
			posicion = 0;  // invalid position, we set on the first one
		}
		evvtCurrentAppOnScreen = posicion;
		evvtAllAppsDataReceived({'icon': instancesid[evvtCurrentAppOnScreen].launcher.iconPath, 'description':instancesid[evvtCurrentAppOnScreen].launcher.shortDescription});
		jQuery("#evvtCanvas").html('<div id="evvtappTitleBar" class="evvtappTitleBar"><div id="evvtappTitleBarTitle" class="evvtappTitleBarTitle"></div><div id="evvtappTitleBarTools" class="evvtappTitleBarTools"></div></div><div id="evvtappContentDiv" class="evvtappContentDiv"></div>');
		instancesid[evvtCurrentAppOnScreen].show();
	}	
    return false;
};

function jumpToMenu() {
	$("#vtappStatus").show();
	appidlist = '';
	for(var launch=0; launch<vtApps.launchers.length; launch++) {
		if (!vtApps.launchers[launch].canshow) continue;  // can't pick from the ones you can't show
       	for(var inst=0; inst<vtApps.launchers[launch].instances.length; inst++) {
       		if (vtApps.launchers[launch].instances[inst].title!='') continue;
       		appidlist = appidlist + (appidlist!='' ? ',' : '') + vtApps.launchers[launch].instances[inst].id;
       	}
    }
	appidtitle = [];
	if (appidlist!='') {
		ajaxurl = 'index.php?module=evvtApps&action=evvtAppsAjax&file=ajax&evvtapps_action=VTAPP_getManyTitles&appid_list='+appidlist;
   		$.ajax({
   		  url: ajaxurl,
   		  async:false
   		}).done(function(apptitles) {
   			appidtitle = jQuery.parseJSON(apptitles);
   		});
		
	}
	var dataobj = [];
	var numinst = 0;
	for(var launch=0; launch<vtApps.launchers.length; launch++) {
       	for(var inst=0; inst<vtApps.launchers[launch].instances.length; inst++) {
       		atitle = vtApps.launchers[launch].instances[inst].title;
       		if (atitle=='') {
       			atitle = appidtitle[vtApps.launchers[launch].instances[inst].id];
       			vtApps.launchers[launch].instances[inst].title = atitle;
       		}
       		data = {"listid": numinst, "icon": vtApps.launchers[launch].iconPath, "title": atitle};
       		dataobj.push(data);
       		numinst++;
       	}
    }
	var DDapplistTimer;
	$("#vtappDDListDiv").show();
	$("#vtappDDListInput").show();
    $("#vtappDDListInput").kendoDropDownList({
        dataTextField: "title",
        dataValueField: "listid",
        template: '<span style="vertical-align:middle;"><img src="${ data.icon }" height="16px"/>&nbsp;${ data.title }</span>',
        dataSource: dataobj,
        change: function(e) {
            move2App(e.sender.selectedIndex);
            this.close();
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

function getDashboardAssignedAppsFromTree () {
	var result = [];
	treeview = $("#evvtDashboardEditorTreeview").data("kendoTreeView");
	treeview._processNodes('.k-item', function (index, item) {
      	atributos = $(item).find("> div span span[atributos]");
      	if (!jQuery.isEmptyObject(atributos)) {
	      	var splitprops = jQuery.parseJSON(atributos.attr("splitprops"));
	      	if (splitprops.vtappid>0) {
	      		result.push(splitprops.vtappid);
	      	}
      	}
      });
	return result;
}

function getNumberOfElementsFromTree() {
	return $("#evvtDashboardEditorTreeview").data("kendoTreeView").element.find('li').length;
}

function getMaxElementsIDFromTree() {
	var maxid = 1;
	$("#evvtDashboardEditorTreeview").data("kendoTreeView").element.find('span[atributos]').each(function(idx,attrelem) {
		var elemid = parseInt($(this).attr('id').substring(14));
		if (maxid<elemid) maxid = elemid;
	});
	return maxid;
}

function getAppDropdownData() {
	dbAssignedApps = getDashboardAssignedAppsFromTree();
	appidlist = '';
	for(var launch=0; launch<vtApps.launchers.length; launch++) {
		if (!vtApps.launchers[launch].canshow) continue;  // can't pick from the ones you can't show
       	for(var inst=0; inst<vtApps.launchers[launch].instances.length; inst++) {
       		if (vtApps.launchers[launch].instances[inst].title!='') continue;
       		appidlist = appidlist + (appidlist!='' ? ',' : '') + vtApps.launchers[launch].instances[inst].id;
       	}
    }
	appidtitle = [];
	if (appidlist!='') {
		ajaxurl = 'index.php?module=evvtApps&action=evvtAppsAjax&file=ajax&evvtapps_action=VTAPP_getManyTitles&appid_list='+appidlist;
   		$.ajax({
   		  url: ajaxurl,
   		  async:false
   		}).done(function(apptitles) {
   			appidtitle = jQuery.parseJSON(apptitles);
   		});
		
	}
	dataobj = [];
	/*
	 * action
	 *   0 empty the selected pane
	 *   1 new instance in the selected pane
	 *   2 assign instance to the selected pane
	 *   3 swap instance with the selected pane
	 *  0, 1 and 2, will unassign any currently assigned instance, leaving it free to be assigned elsewhere
	 */
	var data = {"operation": "modules/evvtApps/images/blank.png", "vtappid": 0, "action": 0, "icon":"modules/evvtApps/images/blank.png", "title": vtApps.launchers[0].translate('NotAssigned') };
	dataobj.push(data);
	for(var launch=0; launch<vtApps.launchers.length; launch++) {
		if (!vtApps.launchers[launch].canshow) continue;  // can't pick from the ones you can't show
		if (vtApps.launchers[launch].clonable) {
			data = {"operation": "modules/evvtApps/images/assignapp16.png", "vtappid": launch, "vtlaunch": launch, "action": 1, "icon": vtApps.launchers[launch].iconPath, "title": vtApps.launchers[launch].shortDescription };
			dataobj.push(data);
		}
       	for(var inst=0; inst<vtApps.launchers[launch].instances.length; inst++) {
       		atitle = vtApps.launchers[launch].instances[inst].title;
       		if (atitle=='') {
       			atitle = appidtitle[vtApps.launchers[launch].instances[inst].id];
       			vtApps.launchers[launch].instances[inst].title = atitle;
       		}
       		if (dbAssignedApps.length>0 && jQuery.inArray(vtApps.launchers[launch].instances[inst].id,dbAssignedApps)>-1) {
       			action = 3; // swap with currently assigned app
       			actionimg = "swap.gif";
       		} else {
       			action = 2; // assign, overwriting currently assigned app and leaving it free if it exists
       			actionimg = "assignapp16.png";
       		}
       		data = {"operation": "modules/evvtApps/images/"+actionimg, "vtappid": vtApps.launchers[launch].instances[inst].id, "vtlaunch": launch, "vtinst": inst, "action": action, "icon": vtApps.launchers[launch].iconPath, "title": atitle};
       		dataobj.push(data);
       	}
    }
	return dataobj;
}

function assignAppDropdown() {
	$("#vtappStatus").show();
	dataobj = getAppDropdownData();
	$("#evvtSplitvtAppid").show();
    $("#evvtSplitvtAppid").kendoDropDownList({
        dataTextField: "title",
        dataValueField: "vtappid",
        template: '<span style="vertical-align:middle;"><img src="${ data.operation }" height="16px"/>&nbsp;&nbsp;&nbsp;&nbsp;<img src="${ data.icon }" height="16px"/>&nbsp;${ data.title }</span>',
        dataSource: dataobj,
        select: function(e) {
            var treeview = $("#evvtDashboardEditorTreeview").data("kendoTreeView");
            tvsel = treeview.select();
            if (!jQuery.isEmptyObject(tvsel)) {
	            var atributos = tvsel.find("> div span span[atributos]");
	            if (!jQuery.isEmptyObject(atributos)) {
	            	activateSaveRefreshButton();
	                divid = atributos.attr('id').substring(14);
	                divappid = '#evvtappContentDiv-' + divid;
	            	appinfo = this.dataItem(e.item.index());
		            var splitprops = jQuery.parseJSON(atributos.attr("splitprops"));
		            switch (appinfo.action) {
		            case 0:  // 0 empty the selected pane. will unassign any currently assigned instance, leaving it free to be assigned elsewhere
		            	newdropdownvalue = 0;
		            	splitprops.vtappid = 0;
		            	$(divappid).html('');
		            	$('#evvtappTitleBarTitle-' + divid).html('');
		            	$('#evvtappTitleBarTools-' + divid).html('');
		            	newtext = vtApps.launchers[0].translate('vtApp Container');
		            	newimage = 'evvtdbcol';
		                sprops = JSON.stringify(splitprops);
		                atributos.attr("splitprops",sprops);
		                changeTreenodeContent(tvsel,newtext,newimage);
		            	break;
		            case 1:  // 1 new instance in the selected pane. will unassign any currently assigned instance, leaving it free to be assigned elsewhere
		            	vtApps.launchers[appinfo.vtlaunch].newInstance(divid);
		            	splitprops.vtlaunch = appinfo.vtlaunch;
		            	splitprops.vtinst = vtApps.launchers[appinfo.vtlaunch].instances.length - 1;
		            	splitprops.vtappid = vtApps.launchers[appinfo.vtlaunch].instances[splitprops.vtinst].id;
		            	newtext = vtApps.launchers[splitprops.vtlaunch].instances[splitprops.vtinst].title;
		            	newimage = 'evvtdbapp';
		                sprops = JSON.stringify(splitprops);
		                atributos.attr("splitprops",sprops);
		                changeTreenodeContent(tvsel,newtext,newimage);
		                newdropdownvalue = splitprops.vtappid;
		            	break;
		            case 2:  // 2 assign instance to the selected pane. will unassign any currently assigned instance, leaving it free to be assigned elsewhere
		            	splitprops.vtappid = appinfo.vtappid;
		            	newtext = vtApps.launchers[appinfo.vtlaunch].instances[appinfo.vtinst].title;
		            	newimage = 'evvtdbapp';
		                sprops = JSON.stringify(splitprops);
		                atributos.attr("splitprops",sprops);
		                changeTreenodeContent(tvsel,newtext,newimage);
		            	vtApps.launchers[appinfo.vtlaunch].instances[appinfo.vtinst].show();
		            	newdropdownvalue = splitprops.vtappid;
		            	break;
		            case 3:  // 3 swap instance with the selected pane
		            	swpattrinfo = treeview.element.find("span[splitprops*='vtappid"+'":"'+appinfo.vtappid+"']");
		            	swpnode = swpattrinfo.closest('li');
		            	swpattr = jQuery.parseJSON(swpattrinfo.attr("splitprops"));
		            	swpattr.vtappid = splitprops.vtappid;
		            	swpdivid =  swpattrinfo.attr('id').substring(14);
		                sprops = JSON.stringify(swpattr);
		                swpattrinfo.attr("splitprops",sprops);
		                swptext = tvsel.find('div > span').text().replace(/\n|\t/g,'').trim();
		                swpclass = tvsel.find('div > span > span:first');
		                if (swpclass.hasClass('evvtdbapp')) {
		                	swpcls = 'evvtdbapp';
		                } else if (swpclass.hasClass('evvtdbcol')) {
		                	swpcls = 'evvtdbcol';
		                } else {
		                	swpcls = 'evvtdbrow';
		                }
		                changeTreenodeContent(swpnode,swptext,swpcls);
		            	splitprops.vtappid = appinfo.vtappid;
		            	newtext = vtApps.launchers[appinfo.vtlaunch].instances[appinfo.vtinst].title;
		            	newimage = 'evvtdbapp';
		                sprops = JSON.stringify(splitprops);
		                atributos.attr("splitprops",sprops);
		                changeTreenodeContent(tvsel,newtext,newimage);
		                if (appinfo.vtappid>0) {
		                	vtApps.launchers[appinfo.vtlaunch].instances[appinfo.vtinst].divid = divid;
		                	vtApps.launchers[appinfo.vtlaunch].instances[appinfo.vtinst].show();
		                }
		                if (swpattr.vtappid>0) {
		                	showvtAppWithId(swpattr.vtappid,swpdivid);
		                } else {
		            		$('#evvtappContentDiv-'+swpdivid).html('');
		                }
		                newdropdownvalue = splitprops.vtappid;
		            	break;
		            }
	                this.dataSource.data(getAppDropdownData());
	                //this.dataSource.read();
	                this.value(newdropdownvalue);
	            }
            }
        }
    });
    var dropdownlist = $("#evvtSplitvtAppid").data("kendoDropDownList");
    dropdownlist.list.width(400);
    $("#vtappStatus").hide();
}

function changeTreenodeContent(treenode,newtext,newimage) {
	treenode.find('div > span > span:first').removeClass('evvtdbcol evvtdbrow evvtdbapp').addClass(newimage);
	htmlspan = treenode.find('div > span');
	html1 = htmlspan.html().substring(0,htmlspan.html().indexOf('</span>')+7)+newtext;
	html2 = htmlspan.html().substring(htmlspan.html().indexOf('<span id'));
	htmlspan.html(html1+html2);
}

function showvtAppWithId(vtappid,swpdivid) {
	found = false;
	for(var launch=0; launch<vtApps.launchers.length; launch++) {
       	for(var inst=0; inst<vtApps.launchers[launch].instances.length; inst++) {
       		found = (vtApps.launchers[launch].instances[inst].id==vtappid);
       		if (found) break;
       	}
       	if (found) break;
    }
	if (found) {
		vtApps.launchers[launch].instances[inst].divid = swpdivid;
		vtApps.launchers[launch].instances[inst].show();
	} else {
		vtApps.launchers[launch].instances[inst].divid = 0;
		$('#evvtappContentDiv-'+swpdivid).html('');
	}
}

function getdbproperty(treenode,attrname) {
	attrvalue = '';
    var atributos = treenode.find("> div span span[atributos]");
    if (!jQuery.isEmptyObject(atributos)) {
    	splitprops = jQuery.parseJSON(atributos.attr("splitprops"));
    	attrvalue = splitprops[attrname];
    }
    return attrvalue;
}

function getAlldbproperty(treenode) {
	splitprops = {};
    var atributos = treenode.find("> div span span[atributos]");
    if (!jQuery.isEmptyObject(atributos)) {
    	splitprops = jQuery.parseJSON(atributos.attr("splitprops"));
    }
    return splitprops;
}

function setdbproperty(treenode,attrname,attrvalue) {
    var atributos = treenode.find("> div span span[atributos]");
    if (!jQuery.isEmptyObject(atributos)) {
    	splitprops = jQuery.parseJSON(atributos.attr("splitprops"));
        splitprops[attrname] = attrvalue;
        sprops = JSON.stringify(splitprops);
        atributos.attr("splitprops",sprops);
        activateSaveRefreshButton();
    }
}

function setdbpropertyToTreeSelected(attrname,attrvalue) {
    treeview = $("#evvtDashboardEditorTreeview").data("kendoTreeView");
    tvsel = treeview.select();
    if (!jQuery.isEmptyObject(tvsel)) {
    	setdbproperty(tvsel,attrname,attrvalue);
    }
}

function setdbpropertySizeToTreeBranch(treeview, root, treenodeselected) {
    root = root || treeview.element.children(".k-group");
    return root.children('ul').children('li').map(function() {
        var atributos = $(this).find("> div span span[atributos]");
        var splitprops = jQuery.parseJSON(atributos.attr('splitprops'));
        var divid = atributos.attr('id').substring(14);
        var divappid = '#evvtdbappdiv-' + divid;

        if (splitprops['evvttype']=='group') {
        	newsize = $(divappid).height();
        } else {
        	newsize = $(divappid).width();
        }
        splitprops['splitSize'] = newsize;
        sprops = JSON.stringify(splitprops);
        atributos.attr("splitprops",sprops);
        
		if (divid == treenodeselected) {
			numerictextbox = $("#evvtSplitSize").data("kendoNumericTextBox");
		    numerictextbox.value(newsize);
		}

		setdbpropertySizeToTreeBranch(treeview, $(this).children(".k-group"), treenodeselected);
      });
}

function showdbproperties(atributos) {
    if (!jQuery.isEmptyObject(atributos)) {
        splitprops = jQuery.parseJSON(atributos.attr("splitprops"));
        // I'm counting that all these values are set to their defaults when they are sent from the database
        dropdownlist = $("#evvtSplitvtAppid").data("kendoDropDownList");
        dropdownlist.value(splitprops['vtappid']);
        numerictextbox = $("#evvtSplitSize").data("kendoNumericTextBox");
        numerictextbox.value(splitprops['splitSize']);
        numerictextbox = $("#evvtSplitMax").data("kendoNumericTextBox");
        numerictextbox.value(splitprops['splitMax']);
        numerictextbox = $("#evvtSplitMin").data("kendoNumericTextBox");
        numerictextbox.value(splitprops['splitMin']);
		$("#evvtSplitResize").attr('checked',splitprops['splitResize']);
		$("#evvtSplitScroll").attr('checked',splitprops['splitScroll']);
		$("#evvtSplitCollapsed").attr('checked',splitprops['splitCollapsed']);
		$("#evvtSplitCollapsable").attr('checked',splitprops['splitCollapsable']);
    }
}

function doCollapsedChange(valor,collapseit) {
	setdbpropertyToTreeSelected('splitCollapsed',valor);
	if (collapseit) {
		editdiv = $('#evvteditingdiv').val();
		var splitter = $(editdiv).parents('div:first').data("kendoSplitter");
		if (valor) {
			splitter.collapse(editdiv);
		} else {
			splitter.expand(editdiv);
		}
	} else {
		$('#evvtSplitCollapsed').attr('checked',valor);
	}
}

function doCollapsibleChange(valor) {
	setdbpropertyToTreeSelected('splitCollapsable',valor);
	editdiv = $('#evvteditingdiv').val();
	var splitter = $(editdiv).parents('div:first').data("kendoSplitter");
	var paneConfig = $(editdiv).data("pane");
    paneConfig['collapsible'] = valor;
	splitter._updateSplitBars();
}

function doSizeChange(valor) {
	if (!evvtDoingDashboardPaint) {
		evvtDoingDashboardPaint = true;
		setdbpropertyToTreeSelected('splitSize',valor);
		editdiv = $('#evvteditingdiv').val();
		var splitter = $(editdiv).parents('div:first').data("kendoSplitter");
		splitter.size(editdiv,valor+'px');
		evvtDoingDashboardPaint = false;
	}
	return true;
};

function doSplitterSizeChange(splitterdiv) {
	if (!evvtDoingDashboardPaint) {
		evvtDoingDashboardPaint = true;
		activateSaveRefreshButton();
		var treeview = $("#evvtDashboardEditorTreeview").data("kendoTreeView");
		var divappid = splitterdiv.substring(splitterdiv.indexOf('-')+1);
		var root = $('#evvtdbappdata-'+divappid).closest('li');
		var treenodeselected = treeview.select();
		if (treenodeselected.length==0) {
			selectTreeElement(root,true);
			treenodeselected = root;
		}
        var atributos = treenodeselected.find("> div span span[atributos]");
        var treenodeseldivid = atributos.attr('id').substring(14);
	    if (!jQuery.isPlainObject(treenodeselected)) {
	    	setdbpropertySizeToTreeBranch(treeview, root, treenodeseldivid);
	    }
		$('#evvtdbhighlightpane').width($('#evvtdbappdiv-'+treenodeseldivid).width()-5);
		$('#evvtdbhighlightpane').height($('#evvtdbappdiv-'+treenodeseldivid).height()-5);
		evvtDoingDashboardPaint = false;
	}
	return true;
};

function doMaxMinChange(valor,maximo) {
	var numeric = $("#evvtSplitSize").data("kendoNumericTextBox");
	var dosizechange = false;
	if (maximo) {
		setdbpropertyToTreeSelected('splitMax',valor+'px');
		numeric.max(valor);
		if (numeric.value()>valor) dosizechange = true;
	} else {
		setdbpropertyToTreeSelected('splitMin',valor+'px');
		numeric.min(valor);
		if (numeric.value()<valor) dosizechange = true;
	}
	if (dosizechange) {
		numeric.value(valor);
		doSizeChange(valor);
	}
}

function doResizeChange(valor) {
	setdbpropertyToTreeSelected('splitResize',valor);
	editdiv = $('#evvteditingdiv').val();
	var splitter = $(editdiv).parents('div:first').data("kendoSplitter");
	var paneConfig = $(editdiv).data("pane");
    paneConfig['resize'] = valor;
    paneConfig['resizable'] = valor;
	splitter._updateSplitBars();
}

function doScrollChange(valor) {
	setdbpropertyToTreeSelected('splitScroll',valor);
	editdiv = $('#evvteditingdiv').val();
	var paneConfig = $(editdiv).data("pane");
    paneConfig['scroll'] = valor;
    paneConfig['scrollable'] = valor;
    $(editdiv).toggleClass("k-scrollable", valor);
}

function evvttriggerResize() {
    $("#evvtDashboardLayout").data("kendoSplitter").trigger("resize");
}

function changeDefaultCanvas(newcanvas) {
	ajaxurl = 'index.php?module=evvtApps&action=evvtAppsAjax&file=ajax&evvtapps_action=VTAPP_setCanvasDefault';
	$.ajax({
	  url: ajaxurl,
	  type: "POST",
	  data: 'evvtcanvas=' + newcanvas
	}).done(function() {
		defaultcanvas = newcanvas;
		setDefaultCanvasImage();
	});
}

function setDefaultCanvasImage() {
	$('#defaultcanvasimg1, #defaultcanvasimg2, #defaultcanvasimg3').attr('src','modules/evvtApps/images/blank.png');
	switch (defaultcanvas) {
	case 'windows':
		$('#defaultcanvasimg1').attr('src','modules/evvtApps/images/selectedcanvas.png');
		break;
	case 'dashboard':
		$('#defaultcanvasimg2').attr('src','modules/evvtApps/images/selectedcanvas.png');
		break;
	case 'allapps':
		$('#defaultcanvasimg3').attr('src','modules/evvtApps/images/selectedcanvas.png');
		break;
	}
}

/*
 * Joe 2-Sep-2012
 * This function creates a Splitter PANES object which permits to manipulate all the panes contained in a splitter
 * It is in a basic version, it would need to be enhanced with some additional properties for each pane and some
 * additional methods, but it works correctly.
 * In the end version of the dashboard I don't need this object, due to the way the DB layout editor manipulates
 * each pane individually it made no sense to load all it's brother panes to be able to makes changes in one pane
 * so I create the DB layout with direct pane manipulation and this object was not used.
 * I comment it out to ease the load in memory but leave it in case it is needed in the future.

function getSplitterPanes(splitter) {
	var pns = (function($) {
	    
    // This is the main object that handles everything
    function panes() {
      this.init();
    }
    panes.prototype = {
    	panes: null,
    	splitter: null,
    	init: function() { 
    		this.panes = $('#'+splitter).children('.k-pane');
    		this.splitter = $('#'+splitter).data("kendoSplitter");
    	},
    	getPane: function (index) {
	        index = Number(index);
	        if(!isNaN(index) && index < this.panes.length) {
	            return this.panes[index];
	        }
	    },
	    setSize: function (index,newsize) {
	        index = Number(index);
	        if(!isNaN(index) && index < this.panes.length) {
	        	this.splitter.size(this.panes[index], newsize+'px');
	        }
	    },
	    setMinSize: function (index,newsize) {
	        index = Number(index);
	        if(!isNaN(index) && index < this.panes.length) {
	        	this.splitter.min(this.panes[index], newsize+'px');
	        }
	    },
	    setMaxSize: function (index,newsize) {
	        index = Number(index);
	        if(!isNaN(index) && index < this.panes.length) {
	        	this.splitter.max(this.panes[index], newsize+'px');
	        }
	    }
    };
    return new panes();
	})(jQuery);
	return pns;
}
 * 
 */

function toggleDashboardEditor() {
  var jWindow = $('#evvtDashboardEditorWindow');
  var kWin = jWindow.data('kendoWindow');
  if (kWin) {
    if (jWindow.data('visibility')) {
      kWin.close();
      jWindow.data('visibility', false);
      $('div[evvttype="item"]').unbind('click');
      $('#evvtdbhighlightpane').remove();
    }
    else {
      kWin.open();
      kWin.toFront();
      jWindow.data('visibility', true);
  	$('div[evvttype="item"]').each(function (idx,item) {
		$(item).bind('click',function (e) {
			divappid = this.id.substring(this.id.indexOf('-')+1);
			selectTreeElement($('#evvtdbappdata-'+divappid).closest('li'),true);
		});
	});
    }
  }
}

function hideDashboardEditor() {
  var jWindow = $('#evvtDashboardEditorWindow');
  var kWin = jWindow.data('kendoWindow');
  if (kWin && jWindow.data('visibility')) {
    jWindow.data('closedByButton', true);
    kWin.close();
  }
  $('#evvtDashboardEditorButton').hide();
  $('div[evvttype="item"]').unbind('click');
  $('#evvtdbhighlightpane').remove();
}

function showDashboardEditor() {
  var jWindow = $('#evvtDashboardEditorWindow');
  var kWin = jWindow.data('kendoWindow');
  if (kWin && jWindow.data('visibility')) {
    kWin.open();
    kWin.toFront();
  }
  $('#evvtDashboardEditorButton').show();
}
