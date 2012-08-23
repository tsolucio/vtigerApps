{
  onRefresh: function() {
	  this.get('#urlbutton').click($.proxy(function() { this._saveUrl(); }, this));
  },
  onEdit: function() { this.get('#urldiv').css('display', 'block'); this.get('#urlname').focus(); },
  _saveUrl: function() {
	  var url = this.get('#urlname').val();
	  this.ajaxRequest('setWebsite', [ url ], function() { this.refresh(); } ); 
  }
}