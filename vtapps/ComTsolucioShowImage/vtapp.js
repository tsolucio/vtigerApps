{
  onRefresh: function() {
	  this.get('#urlbutton').click($.proxy(function() { this._saveUrl(); }, this));
	  this.get('#companyinfo').change($.proxy(function() { this._saveCompanyInfo(); }, this));
  },
  onEdit: function() { this.get('#urldiv').css('display', 'block'); this.get('#urlname').focus(); },
  _saveUrl: function() {
	  var url = this.get('#urlname').val();
	  this.ajaxRequest('setImageUrl', [ url ], function() { this.refresh(); } ); 
  },
  _saveCompanyInfo: function() {
	  var chk = this.get('#companyinfo').attr('checked');
	  this.ajaxRequest('setCompanyInfo', [ chk ], function() { this.refresh(); } ); 
  }
}