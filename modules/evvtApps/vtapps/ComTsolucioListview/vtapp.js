{
  onRefresh: function() {
	  this.get('#modulename').change($.proxy(function() { this._changeFilters(); }, this));
	  this.get('#viewname').change($.proxy(function() { this._saveConfig(); }, this));
	  this.get('#lvpagesize').blur($.proxy(function() { this._saveConfig(); }, this));
	  this.get('#editpin').click($.proxy(function() { this._changePin(); }, this));
	  var colinfo = $.parseJSON(this.get('#gridData').html());
	  var gridpagesize = this.get('#lvpagesize').val();
	  this.get("#grid").kendoGrid({
        dataSource: {
	        type: "json",
	        transport: {
	            read: {
		            url: 'index.php?module=evvtApps&action=evvtAppsAjax&file=ajax&evvtapps_action=getListElements&evvtapps_appid='+this.id,
		            dataType: "json"
	            }
	        },
	        schema: {
	       	    data: "results",
	       	    total: "total"
	        },
	        pageSize: gridpagesize,
	        serverPaging: true,
	        serverFiltering: false,
	        serverSorting: true
        },
        height: '100%',
        filterable: false,
        sortable: {
	        mode: "multiple",
	        allowUnsort: true
        },
        pageable: true,
        columns: colinfo
	  });
  },
  onEdit: function() { this.get('#editgrid').toggle(); },
  _changeFilters: function() {
	  var module = this.get('#modulename').val();
	  this.ajaxRequest('changeFilterList', [ module ], function() { this.refresh(); } ); 
  },
  _changePin: function() {
	  this.ajaxRequest('changePin', [ ], function(newimage) { this.get('#editpin').attr('src',newimage); } ); 
  },
  _saveConfig: function() {
	  var module = this.get('#modulename').val();
	  var filter = this.get('#viewname').val();
	  var pagesize = this.get('#lvpagesize').val();
	  this.ajaxRequest('setFilter', [ module, filter, pagesize ], function() { this.refresh(); } ); 
  }
}