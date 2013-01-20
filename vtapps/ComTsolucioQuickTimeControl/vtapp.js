{
  onRefresh: function() {
	  var crmid_field = this.get("#crmid");
	  var crmmod_field = this.get("#crmmodule");
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
          	crmid_field.val(dataItem.crmid);
          	crmmod_field.val(dataItem.crmmodule);
          }
      });
  }
}