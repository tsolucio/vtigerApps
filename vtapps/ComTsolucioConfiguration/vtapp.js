{
  onRefresh: function() {
    var dsvtappusers = new kendo.data.DataSource({
        type: "json",
        transport: {
          read: $.proxy(function(options) {
              var params = [ this.get('#app-id').val() ];
              this.ajaxRequest('getUserAppConfig', params, function(data) { options.success(data); }, this);
          }, this),
          update: $.proxy(function(options) {
              var params = [ this.get('#app-id').val(), options ];
              this.ajaxRequest('setUserAppConfig', params, function(data) { options.success(data); }, this);
          }, this),
          parameterMap: function(options, operation) {
              if (operation !== "read" && options.models) {
                  return {models: kendo.stringify(options.models)};
              }
          }
        },
        schema: {
          data: "results",
          total: "total",
          model: {
            id: "evvtappsuserid",
            fields: {
              evvtappsuserid: { editable: false, nullable: false },
              appUser: { type: "string" },
              appVisible: { type: "boolean" },
              appEnabled: { type: "boolean" },
              appWrite: { type: "boolean" },
              appHide: { type: "boolean" },
              appShow: { type: "boolean" }
            }
          }
        },
        //serverPaging: true,
        //serverSorting: true,
        //serverFiltering: true,
        pageSize:15
        //filter: { field: "appID", operator: "eq", value: e.data.appID }
      });
      this.get("#grid").kendoGrid({
          dataSource: dsvtappusers,
          scrollable: false,
          sortable: true,
          pageable: true,
          toolbar: ["save", "cancel"],
          editable: true,
          autoBind: false,
          columns: [
            { field: "appUser", title: this.translate('User') },
            { field: "appVisible", title: this.translate('Visible') },
            { field: "appEnabled", title: this.translate('Enabled') },
            { field: "appWrite", title: this.translate('Write') },
            { field: "appHide", title: this.translate('Hide') },
            { field: "appShow", title: this.translate('Show') }
          ]
      });
    if (this.get('#app-id')) {
      this.get('#app-id').change($.proxy(onChange, this));
    }
    function onChange() {
      this.ajaxRequest('getAppUserData', [ this.get('#app-id').val() ], $.proxy(dataReceived, this));
    }
    function dataReceived(data) {
      this.get('#app-icon').attr('src', data.icon);
      this.get('#app-description').html(data.description);
      dsvtappusers.read();
    }
  }
}