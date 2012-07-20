{
  onLoad: function() {
    this.addListener('com.tsolucio.Demo1.doSomething', this._message);
  },
  _message: function(key) {
    this.refresh();
  },
  onRefresh: function() {
    new Highcharts.Chart({
        chart: {
          renderTo: this.get('#chart').attr('id'),
          plotBackgroundColor: null,
          plotBorderWidth: null,
          plotShadow: false
        },
        title: {
          text: this.get('#chart').attr('title')
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
            name: this.get('#potData').attr('title'),
            data: $.parseJSON(this.get('#potData').html())
        }]
    });
  },
  onEdit: function() {}
}