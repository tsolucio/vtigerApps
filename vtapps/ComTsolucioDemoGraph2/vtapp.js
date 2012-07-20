{
  onRefresh: function() {
    this.get('#chart').kendoChart({
        title: {
          text: this.get('#chart').attr('title')
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
  }
}