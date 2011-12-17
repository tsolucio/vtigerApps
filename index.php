<?php
global $current_language;
$mypath="modules/$currentModule";
include_once "$mypath/processConfig.php";
include_once "$mypath/vtapps/baseapp/vtapp.php";
?>
<link href="<?php echo $mypath; ?>/styles/evvtapps.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $mypath; ?>/styles/kendo.common.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $mypath; ?>/styles/kendo.default.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $mypath; ?>/styles/tipsy.css" rel="stylesheet" type="text/css" />
<script src="<?php echo $mypath; ?>/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/js/kendo.all.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/js/jquery.tipsy.js" type="text/javascript"></script>
<script src="<?php echo $mypath; ?>/js/evvtapps.js" type="text/javascript"></script>
            <div id="window1">
                <div id="chart1"></div>
            </div>
            <div id="window2">
                <div id="chart2"></div>
            </div>

            <span id="undo1" style="display:none" class="k-group">Click here to open the window1.</span>
            <span id="undo2" style="display:none" class="k-group">Click here to open the window2.</span>

            <script>
            jQuery(document).ready(function() {
                    var window1 = jQuery("#window1"),
                        undo1 = jQuery("#undo1")
                                .bind("click", function() {
                                    window.data("kendoWindow1").open();
                                    undo1.hide();
                                });
                    var window2 = jQuery("#window2"),
                    undo2 = jQuery("#undo2")
                            .bind("click", function() {
                                window.data("kendoWindow2").open();
                                undo2.hide();
                            });

                    var onClose1 = function() {
                        undo1.show();
                    }
                    var onClose2 = function() {
                        undo2.show();
                    }

                    if (!window1.data("kendoWindow1")) {
                        window1.kendoWindow({
                            width: "500px",
                            title: "Kendo Window1",
                            actions: ["Edit","Refresh", "Maximize", "Close"],
                            close: onClose1
                        });
                    }
                    if (!window2.data("kendoWindow2")) {
                        window2.kendoWindow({
                            width: "500px",
                            top: "100px",
                            title: "Kendo Window2",
                            actions: ["Edit","Refresh", "Maximize", "Close"],
                            close: onClose2
                        });
                    }
                    jQuery("#chart1").kendoChart({
                        title: {
                            text: "My Chart Title1"
                        },
                        series: [
                            {
                                name: "Series 1",
                                data: [200, 450, 300, 125],
                                type: "pie"
                            }
                        ],
                        categoryAxis: {
                            categories: [2000, 2001, 2002, 2003]
                        }
                    });
                    
                    jQuery("#chart2").kendoChart({
                        title: {
                            text: "My Chart Title2"
                        },
                        series: [
                            {
                                name: "Series 1",
                                data: [200, 450, 300, 125]
                            }
                        ],
                        categoryAxis: {
                            categories: [2000, 2001, 2002, 2003]
                        }
                    });
                    
                });
            </script>

            <style scoped="scoped">
                #undo {
                    text-align: center;
                    position: absolute;
                    white-space: nowrap;
                    border-width: 1px;
                    border-style: solid;
                    padding: 2em;
                    cursor: pointer;
                }
            </style>
<div id="evvtCanvas" class="evvtCanvas">
<?php
$rsapps=$adb->query('select evvtappsid from vtiger_evvtapps');
$numapps=$adb->num_rows($rsapps);
for ($app=2;$app<=$numapps;$app++) {  // jump app1 which MUST be Trash Can and we will put it at the end
	$appid=$adb->query_result($rsapps,$app-1,'evvtappsid');
	$loadedclases=get_declared_classes();
	include_once "$mypath/vtapps/app$appid/vtapp.php";
	$newclass=array_diff(get_declared_classes(), $loadedclases);
	$newclass=array_pop($newclass);
	$newApp=new $newclass($appid);
	$divid="evvtapp$appid";
	echo "<div id='$divid' class='evvtappbox'
	       title='<b>".$newApp->getAppName($current_language)."</b><br>".$newApp->getTooltipDescription($current_language)."'
	       onclick='evvtappsOpenWindow($appid,\"$newclass\",".$newApp->getAppInfo($current_language).")'>
	       <img src='".$newApp->getAppIcon()."'></div>";
	echo '<script language="javascript">';
	echo "$('#$divid').tipsy($tipsy_settings);";
	if ($newApp->canDelete()) {
		echo "var draggable$divid = $('#$divid').kendoDraggable({
                        hint: function() {
                        	var imgclone=$('#$divid').clone();
                        	imgclone.css({margin: -40});
                            return imgclone;
                        }});";
	}
	echo '</script>';
}
// Now we do Trash Can, at the end
include_once "$mypath/vtapps/app1/vtapp.php";
$newApp=new vtAppcomTSolucioTrash(1);
?>
<div id='evvtapptrash' class='evvtappbox' title='<b><?php echo $newApp->getAppName($current_language); ?></b><br><?php echo $newApp->getTooltipDescription($current_language); ?>'><img src='<?php echo $newApp->getAppIcon(); ?>'></div>
<script language="javascript">
$("#evvtapptrash").tipsy(<?php echo $tipsy_settings; ?>);
var trashTarget = $("#evvtapptrash").kendoDropTarget();
</script>
</div> <!-- evvtCanvas -->
