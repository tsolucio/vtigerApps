{literal}
<div id="grid"></div>
<script>
    $("#grid").kendoGrid({
        dataSource: {
            data: {/literal}{$APPS}{literal},
            schema: {
                model: {
                    fields: {
                    	appID: { type: "string" },
                        appName: { type: "string" },
                        appDescription: { type: "string" }
                    }
                }
            },
            pageSize: 10
        },
        height: 550,
        scrollable: true,
        sortable: true,
        filterable: true,
        pageable: true,
        detailInit: detailInit,
        dataBound: function() {
            this.expandRow(this.tbody.find("tr.k-master-row").first());
        },
        columns: [
            {
                field: "appID",
                title: "{/literal}{$LBLappID}{literal}",
                width: "80px"
            },
            {
                field: "appName",
                title: "{/literal}{$appName}{literal}",
                width: "280px"
            },
            {
                field: "appDescription",
                title: "{/literal}{$appDescription}{literal}"
            }
        ]
    });
    function detailInit(e) {
        $("<div/>").kendoGrid({
            dataSource: {
                type: "json",
                transport: {
                    read: {
                        url: 'index.php?'+evvtURLp+'&vtappaction=dovtAppMethod&vtappmethod=getUserAppConfig&userappid='+e.data.appID+'&class=vtAppcomTSolucioConfiguration&appid={/literal}{$appId}{literal}',
                        dataType: "json"
                    },
			        update: {
			            url: 'index.php?'+evvtURLp+'&vtappaction=dovtAppMethod&vtappmethod=setUserAppConfig&userappid='+e.data.appID+'&class=vtAppcomTSolucioConfiguration&appid={/literal}{$appId}{literal}',
			            dataType: "json"
			        },
			        parameterMap: function(options, operation) {
                        if (operation !== "read" && options) {
                            return {evvtappsuser: kendo.stringify(options)};
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
            },
            scrollable: false,
            sortable: true,
            pageable: true,
            toolbar: ["save", "cancel"],
            editable: true,
            columns: [ 
                 { field: "appUser", title: "{/literal}{$User}{literal}"},
                 { field: "appVisible", title: "{/literal}{$Visible}{literal}"},
                 { field: "appEnabled", title: "{/literal}{$Enabled}{literal}"},
                 { field: "appWrite", title: "{/literal}{$Write}{literal}"},
                 { field: "appHide", title: "{/literal}{$Hide}{literal}"},
                 { field: "appShow", title: "{/literal}{$Show}{literal}"},
                 { field: "appDelete", title: "{/literal}{$Delete}{literal}"} ]
        }).appendTo(e.detailCell);
    }
</script>
{/literal}
