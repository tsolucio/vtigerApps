{
  onRefresh: function() {
    var datasource = null;
    if (this.get('#app-id')) {
      dataSource = {
        type: "json",
        transport: {
          read: $.proxy(function(options) {
              var params = [ this.get('#app-id') ];
              this.ajaxRequest('getAppUserData', params, function(data) { options.success(data); }, this);
          }, this),
          update: $.proxy(function(options) {
              var params = [ this.get('#app-id') ];
              this.ajaxRequest('setAppUserData', params, function(data) { options.success(data); }, this);
          }, this)
        },
        schema: {
          data: "results",
          total: "total",
          model: {
            id: "evvtappsuserid",
            fields: {
              evvtappsuserid: { editable: false, nullable: false },
              appVisible: { type: "boolean" },
              appEnabled: { type: "boolean" },
              appWrite: { type: "boolean" },
              appHide: { type: "boolean" },
              appShow: { type: "boolean" },
              appDelete: { type: "boolean" }
            }
          }
        },
        //serverPaging: true,
        //serverSorting: true,
        //serverFiltering: true,
        pageSize:15
        //filter: { field: "appID", operator: "eq", value: e.data.appID }
      };
      this.get("#grid").kendoGrid({
          datasource: datasource,
          scrollable: false,
          sortable: true,
          pageable: true,
          toolbar: ["save", "cancel"],
          editable: true,
          columns: [
            { field: "appId", title: this.translate('Application') },
            { field: "appVisible", title: this.translate('Visible') },
            { field: "appEnabled", title: this.translate('Enabled') },
            { field: "appWrite", title: this.translate('Write') },
            { field: "appHide", title: this.translate('Hide') },
            { field: "appShow", title: this.translate('Show') },
            { field: "appDelete", title: this.translate('Delete') }
          ]
      });
    }
    else {
      dataSource = {
        type: "json",
        transport: {
          read: $.proxy(function(options) {
              this.ajaxRequest('getAppUserData', null, function(data) { options.success(data); }, this);
          }, this),
          update: $.proxy(function(options) {
              this.ajaxRequest('setAppUserData', null, function(data) { options.success(data); }, this);
          }, this)
        },
        schema: {
          data: "results",
          total: "total",
          model: {
            id: "evvtappid",
            fields: {
              evvtappid: { editable: false, nullable: false },
              appVisible: { type: "boolean" },
              appEnabled: { type: "boolean" },
              appWrite: { type: "boolean" },
              appHide: { type: "boolean" },
              appShow: { type: "boolean" },
              appDelete: { type: "boolean" }
            }
          }
        }
      };
      this.get("#grid").kendoGrid({
          datasource: datasource,
          scrollable: false,
          sortable: true,
          pageable: true,
          toolbar: ["save", "cancel"],
          editable: true,
          columns: [
            { field: "appUser", title: this.translate('User') },
            { field: "appVisible", title: this.translate('Visible') },
            { field: "appEnabled", title: this.translate('Enabled') },
            { field: "appWrite", title: this.translate('Write') },
            { field: "appHide", title: this.translate('Hide') },
            { field: "appShow", title: this.translate('Show') },
            { field: "appDelete", title: this.translate('Delete') }
          ]
      });
    }
    if (this.get('#app-id')) {
      this.get('#app-id').change($.proxy(onChange, this));
    }
    function onChange() {
      this.ajaxRequest('getAppUserData', [ this.get('#app-id').val() ], $.proxy(dataReceived, this));
    }
    function dataReceived(data) {
      this.get('#app-icon').attr('src', data.icon);
      this.get('#app-description').html(data.description);
    }
  }
}