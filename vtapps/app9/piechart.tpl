<script type="text/javascript" src="{$appPath}/js/highcharts.js"></script>
<script type="text/javascript" src="{$appPath}/js/themes/gray.js"></script>
<script type="text/javascript" src="{$appPath}/js/modules/exporting.js"></script>
<div id="vtAppcomTSolucioPotChart" style="width: 800px; height: 400px; margin: 0 auto"></div>
<script language="javascript">
{literal}
var chart;
chart = new Highcharts.Chart({
	chart: {
		renderTo: 'vtAppcomTSolucioPotChart',
		plotBackgroundColor: null,
		plotBorderWidth: null,
		plotShadow: false
	},
	title: {
		text: '{/literal}{$Title}{literal}'
	},
	tooltip: {
		formatter: function() {
			return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %';
		}
	},
	plotOptions: {
		pie: {
			allowPointSelect: true,
			cursor: 'pointer',
			dataLabels: {
				enabled: false
			},
			showInLegend: true
		}
	},
    series: [{
		type: 'pie',
		name: '{/literal}{$PotShare}{literal}',
		data: {/literal}{$PotData}{literal}
	}]
 });
{/literal}
</script>