{
  onRefresh: function() {
    this.get('#refresh').click($.proxy(this._saveConfig, this));
    this.get('#editpin').click($.proxy(this._changePin, this));
    this.get('#expxls').click($.proxy(this._download, this));
    this.get('#startdate-picker').kendoDatePicker({ format: this.get('#startdate-picker').attr('format')});
    this.get('#enddate-picker').kendoDatePicker({ format: this.get('#enddate-picker').attr('format')});
    this.get("#grid").kendoGrid({
	      dataSource: {
	        type: "json",
	        data: $.parseJSON(this.get('#grid-data').html()),
	        aggregate: $.parseJSON(this.get('#grid-aggregate').html())
	      },
	      scrollable: true,
	      groupable: true,
	      sortable: true,
	      pageable: false,
	      editable: false,
	      autoBind: true,
	      columns: $.parseJSON(this.get('#grid-columns').html())
	  });
  },
  onEdit: function() {
    this.get('#editgrid').toggle();
  },
  _changePin: function() {
	  this.ajaxRequest('changePin', [ ], function(newimage) { this.get('#editpin').attr('src',newimage); } ); 
  },
  _download: function() {
		io = document.createElement('iframe');
		io.src = 'index.php?module=evvtApps&action=evvtAppsAjax&file=ajax&evvtapps_action=getExcelExport&evvtapps_appid='+this.id;
		io.style.display = 'block';
		io = $(io);
		$('body').append(io);
		setTimeout(function() {
			io.remove();
		}, 5000);
		
	},
  _saveConfig: function() {
	  var startdate = this.get('#startdate-picker').val();
	  var enddate = this.get('#enddate-picker').val();
	  var users = this.get('#users').val();
	  this.ajaxRequest('setFilter', [ startdate, enddate, users ], function() { this.refresh(); } ); 
  }
}