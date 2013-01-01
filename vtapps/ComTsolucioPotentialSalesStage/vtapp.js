{
  onRefresh: function() {
	  eval(this.get('#potData').text());
      jQuery.plot(this.get('#chartdiv'), chartData,
		{
			series: {
				pie: {
					show: true,
					radius:1,
					label: {
						show: true,
						radius: 2/3,
						formatter: function(label, series){
							return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
						},
						threshold: 0.1
					}
				}
			},
			legend: {
				show: true
			}
		});
  }
}