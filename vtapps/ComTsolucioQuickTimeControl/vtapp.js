{
  onRefresh: function() {
	  var thisobj = this;
	  this._vtappstopClock();
	  this.get('#tcbtn').click($.proxy(function() { this._tcbtn_click(); }, this));
	  if (this.get('#tcid').val()!=0) this._vtappstartClock();
	  var workwithcrmfld = this.get("#workoncrmid");
	  var billwithcrmfld = this.get("#billwithcrmid");
	  this.get("#uitype10").kendoAutoComplete({
          minLength: 3,
          filter: 'contains',
          dataTextField: "crmname",
          dataSource: {
              type: "json",
              serverFiltering: true,
              serverPaging: true,
              pageSize: 20,
              transport: {
                  read: {
                      url: "index.php?module=evvtApps&action=evvtAppsAjax&file=vtapps/ComTsolucioQuickTimeControl/searchuitype10",
                      dataType:'json'
                  }
              }
          },
          select: function(e) {
              // access the selected item via e.item (jQuery object)
          	var dataItem = this.dataItem(e.item.index());
          	workwithcrmfld.val(dataItem.crmid);
          }
      });
	  this.get("#billwith").kendoAutoComplete({
          minLength: 3,
          filter: 'contains',
          dataTextField: "crmname",
          dataSource: {
              type: "json",
              serverFiltering: true,
              serverPaging: true,
              pageSize: 20,
              transport: {
                  read: {
                      url: "index.php?module=evvtApps&action=evvtAppsAjax&file=vtapps/ComTsolucioQuickTimeControl/searchuitype10&searchinmodules="+encodeURIComponent('Products#Services'),
                      dataType:'json'
                  }
              }
          },
          select: function(e) {
              // access the selected item via e.item (jQuery object)
          	var dataItem = this.dataItem(e.item.index());
          	billwithcrmfld.val(dataItem.crmid);
          }
      });
	  var grid = this.get("#grid").kendoGrid({
	        dataSource: {
		        type: "json",
		        transport: {
		            read: {
			            url: 'index.php?module=evvtApps&action=evvtAppsAjax&file=ajax&evvtapps_action=getMyTCs&evvtapps_appid='+this.id,
			            dataType: "json"
		            }
		        },
		        schema: {
		        	model: {
						id: "tcid",
						fields: {
							tcid: { editable: false, nullable: true },
							tcdate: { },
							horainifin: { },
							tctime: { },
							tcrelto: { },
							tcbillw: { },
							tcopen: { }
						}
		        	},
		       	    data: "results",
		       	    total: "total"
		        },
		        pageSize: 30,
		        serverPaging: true,
		        serverFiltering: false,
		        serverSorting: true
	        },
	        height: '70%',
	        filterable: false,
	        sortable: {
		        mode: "multiple",
		        allowUnsort: true
	        },
	        pageable: true,
            columns: [
                  { field: "tcdate", title:this.translate('tcdate'), encoded: false, width: '70px'},
                  { field: "horainifin", title:this.translate('horainifin'), encoded: false, width: '85px' },
                  { field: "tctime", title:this.translate('tctime'), encoded: false, width: '50px'},
                  { field: "tcrelto", title:this.translate('tcrelto'), encoded: false},
                  { field: "tcbillw", title:this.translate('tcbillw'), encoded: false},
                  { command: [{ text: this.translate('Continue'), className: "small continue-button" }] }
            ],
            editable: false
		  }).data("kendoGrid");
	  this.get("#grid").delegate(".continue-button", "click", function(e) {
          e.preventDefault();
          var dataItem = grid.dataItem(jQuery(this).closest("tr"));
          thisobj._vtappstopClock();
          if (dataItem.tcopen=='1') {
        	  // reload with selected TC
        	  thisobj.ajaxRequest('VTAPP_getContent', [dataItem.tcid], thisobj.redrawContent, thisobj);
          } else {
        	  // duplicate selected TC and load it by refreshing (it will be the latest and thus be loaded by default)
    		  $.ajax({
    			  type: "POST",
    			  url: 'index.php?module=evvtApps&action=evvtAppsAjax&file=ajax&evvtapps_action=saveMyTC&evvtapps_appid='+thisobj.id,
    			  data: {
    				  tcid: dataItem.tcid,
    				  duplicateit: 1
    			    }
    		  	}).done(function( tcid ) {
    		  		thisobj.refresh();
    		  });
          }
      });

  },
  _tcbtn_click: function() {
	  var btn = this.get('#tcbtn');
	  var bck = this.get('#qtcinputs');
	  var tccrmid = this.get('#tcid');
	  var starttime = this.get('#starttime');
	  var stoptime = this.get('#stoptime');
	  var stoptimesecs = this.get('#stoptimesecs');
	  var startdate = this.get('#startdate');
	  var gridobj = this.get("#grid");
	  if (btn.hasClass('stop-button')) {
		  $.ajax({
			  type: "POST",
			  url: 'index.php?module=evvtApps&action=evvtAppsAjax&file=ajax&evvtapps_action=saveMyTC&evvtapps_appid='+this.id,
			  data: {
				  tcid: tccrmid.val(),
				  relto: this.get('#workoncrmid').val(),
				  relcpt: this.get('#tcrelconcept option:selected').val(),
				  billto: this.get('#billwithcrmid').val(),
			    }
		  	}).done(function( tcid ) {
		  		gridobj.data("kendoGrid").dataSource.read();
		  });
		  bck.removeClass('watchon');
		  bck.addClass('watchoff');
		  btn.removeClass('stop-button');
		  btn.addClass('start-button');
		  btn.val(this.translate('LBL_WATCH_START'));
		  starttime.val('');
		  stoptime.val('');
		  stoptimesecs.val('0');
		  this.get('#ttime_seconds').val('0');
		  this.get('#ttime').val('0');
		  tccrmid.val('0');
		  this.get('#billwithcrmid').val('0');
		  this.get('#billwith').val('');
		  this.get('#workoncrmid').val('0');
		  this.get('#uitype10').val('');
		  this.get('#tcrelconcept').val('');
		  startdate.val('');
		  this._vtappstopClock();
	  } else {
		  $.ajax({
			  type: "POST",
			  url: 'index.php?module=evvtApps&action=evvtAppsAjax&file=ajax&evvtapps_action=saveMyTC&evvtapps_appid='+this.id,
			  data: {
				  tcid: 0,
				  relto: this.get('#workoncrmid').val(),
				  relcpt: this.get('#tcrelconcept option:selected').val(),
				  billto: this.get('#billwithcrmid').val(),
			    }
		  	}).done(function( tcid ) {
		  		tccrmid.val(tcid);
		  		gridobj.data("kendoGrid").dataSource.read();
		  });
		var currentDate = new Date()
		var day = currentDate.getDate()
		var month = currentDate.getMonth() + 1
		var year = currentDate.getFullYear()
		var hours = currentDate.getHours()
		var minutes = currentDate.getMinutes()
		if (hours < 10)
			hours = "0" + hours
		if (minutes < 10)
			minutes = "0" + minutes
		this._vtappstartClock();
		  starttime.val(hours + ":" + minutes);
		  stoptime.val(hours + ":" + minutes);
		  stoptimesecs.val(hours * 60 * 60 + minutes * 60);
		  startdate.val(day + "/" + month + "/" + year);
		  bck.addClass('watchon');
		  bck.removeClass('watchoff');
		  btn.addClass('stop-button');
		  btn.removeClass('start-button');
		  btn.val(this.translate('LBL_WATCH_STOP'));
	  }  
  },
  _vtappClockInterval: 0,
  _vtappstartClock: function() {
	  this._vtappClockInterval = setInterval($.proxy(this._vtappupdateClock, this), 1000);
  },
  _vtappstopClock: function() {
	  window.clearInterval(this._vtappClockInterval);
  },
  _vtappupdateClock: function() {
		var clock_display = this.get('#ttime');
		var clock_counter = this.get('#ttime_seconds');
		var end_display = this.get('#stoptime');
		var end_counter = this.get('#stoptimesecs');
		var newtime = parseInt(clock_counter.val())+1;
		clock_counter.val(newtime);
		var newendtime = parseInt(end_counter.val())+1;
		end_counter.val(newendtime);
		var hours = parseInt(newtime / 60 / 60);
		var minutes = parseInt(newtime / 60) % 60;
		if (hours < 10) {
			hours = '0' + hours;
		}
		if (minutes < 10) {
			minutes = '0' + minutes;
		}
		if (newtime % 60 == 0) {
			clock_display.val(hours+':'+minutes);
		} else {
			if (newtime % 2) {
				clock_display.val(hours+' '+minutes);
			} else {
				clock_display.val(hours+':'+minutes);
			}
		}
		var hours = parseInt(newendtime / 60 / 60);
		var minutes = parseInt(newendtime / 60) % 60;
		if (hours < 10) {
			hours = '0' + hours;
		}
		if (minutes < 10) {
			minutes = '0' + minutes;
		}
		end_display.val(hours+':'+minutes);
  }
}