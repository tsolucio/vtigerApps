{
  onRefresh: function() {
    var localData = [
      {ID: "1010000010", MODULE: "Accounts", FROM: "Offline", TO: "Central", DATE: "2012-02-20", TYPE: "create", ERROR: "ok"},
      {ID: "134", MODULE: "Contacts", FROM: "Central", TO: "Offline", DATE: "2012-02-20", TYPE: "delete", ERROR: "ok"},
      {ID: "111", MODULE: "Leads", FROM: "Central", TO: "Offline", DATE: "2012-02-20", TYPE: "conflict", ERROR: "ok"},
    ];
    var dataSource = new kendo.data.DataSource( {
        data: localData,
        schema: {
          model: {
            fields: {
              ID: { type: "number" },
              MODULE: { type: "string" },
              FROM: { type: "string" },
              TO: { type: "string" },
              DATE: { type: "date" },
              TYPE: { type: "string" },
              ERROR:{type:"string"}
            }
          }
        },
        pageSize: 12,
        group: { field: "MODULE" } 
    });
    this.get("#grid").kendoGrid({
        dataSource: dataSource,
        height: 600,
        sortable: {
          mode: "multiple",
          allowUnsort: true
        },
        rowTemplate:  $.proxy(kendo.template(this.get("#template").html()),dataSource),
        groupable:true,
        scrollable: true,
        pageable: true,
        filterable: true
        
    });
  }
}