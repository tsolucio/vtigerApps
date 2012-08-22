var vtApps =
(function($) {
    
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
            /*$("#launchers").sortable({
              update: function() { alert('sorted'); }
            });*/
            $("#launchers").disableSelection();
            for(var i=0; i<data.length; i++) {
              var appLauncher = new this.VtAppLauncher(data[i]);
              this.launchers.push(appLauncher);
              appLauncher.show();
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
        if (this.visible) {
          if ((this.clonable && e.ctrlKey) || this.instances.length==0) {
            this.newInstance();
          }
          else {
            var show = true;
            for(var i=0; i<this.instances.length; i++) {
              if (this.instances[i].isVisible()) {
                this.instances[i].hide();
                show = false;
              }
            }
            if (show) {
              for(var i=0; i<this.instances.length; i++) {
                this.instances[i].show();
              }
            }
          }
        }
      },
      // Request new app instance
      newInstance: function() {
        vtApps.ajaxRequest('VTAPP_createAppInstance', { launcherid: this.id }, function (data) { this.createInstance(data).show(); }, this);
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
      visible: false,
      resizeTimeout: false,
      // Init app instance from data provided
      init: function(launcher, data) {
        this.launcher = launcher;
        this.id = data.id;
        this.top = data.top;
        this.left = data.left;
        this.width = data.width;
        this.height = data.height;
        this.visible = false;
        if (launcher.handlers!=null) {
          $.extend(this, launcher.handlers);
        }
        this.createWindow();
      },
      // Show app window
      show: function() {
        this.refresh();
        $('#'+this.windowId).data('kendoWindow').open();
        kWin = $('#'+this.windowId).data("kendoWindow");
        kWin.toFront();
        this.visible = true;
      },
      // Hide app window
      hide: function() {
        $('#'+this.windowId).data('kendoWindow').close();
        this.visible = false;
      },
      destroy: function() {
        $('#'+this.windowId).data('kendoWindow').destroy();
        $(document).unbind('vta.'+this.id);
      },
      // Get visible state
      isVisible: function() {
        return this.visible;
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
        actions.push('Minimize');
        if (this.launcher.resizable) {
          actions.push('Maximize');
        }
        if (this.launcher.clonable) {
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
        if (this.launcher.visible) {
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
        setTimeout($.proxy(this.onLoad, this), 0);
      },
      // Set window title
      setTitle: function(title) {
        var kWin = $('#'+this.windowId).data("kendoWindow");
        kWin.title(title);
      },
      // Set window content
      setContent: function(content) {
        var kWin = $('#'+this.windowId).data("kendoWindow");
        kWin.content(content);
        var id = this.id;
        $('#'+this.windowId+' *').each(function(index) {
            if ($(this).attr('id')) {
              $(this).attr('id', 'vtapp-id-'+id+'-'+$(this).attr('id'));
            }
        });
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
        return $(selector, $('#'+this.windowId));
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