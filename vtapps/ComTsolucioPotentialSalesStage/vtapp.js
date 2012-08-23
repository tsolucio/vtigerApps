{
  onRefresh: function() {
	  var chartdata = $.parseJSON(this.get('#potData').html());
	  //var chartdata = eval('(' + this.get('#potData').html() + ')');
	  $.jqplot(this.safeId('chartdiv'), [chartdata],
	  {
		  //title: this.get('#chartdiv').attr('title'),
	      seriesDefaults: {
	          // Make this a pie chart.
	          renderer: jQuery.jqplot.PieRenderer,
	          shadow: true,
	          rendererOptions: {
	            // Put data labels on the pie slices.
	            // By default, labels show the percentage of the slice.
	            showDataLabels: true,
	            sliceMargin:10,
	            shadowOffset:1,
	            shadowAlpha:0.5,
	            shadowDepth:5
	          },
	        },
		  highlighter: {
        	  show: true,
        	  showTooltip: false,
        	  tooltipFade: true,
        	  //tooltipLocation:'sw',
        	  useAxesFormatters:false,
        	  bringSeriesToFront: true
        	},
  	      legend: { show:true, location: 'e' }
	  }
	  );
  }
}