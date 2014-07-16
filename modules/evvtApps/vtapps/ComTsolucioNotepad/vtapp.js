{
  onRefresh: function() {
	  this.get('#notebook_div').dblclick($.proxy(function() { this._editContents(); }, this));
	  this.get('#notebook_textarea').blur($.proxy(function() { this._saveNote(); }, this));
  },
  onDestroy: function() {
	  if (confirm(this.translate('EliminateNotes'))) {
		  this.ajaxRequest('preDestroy', [ ]);
		  return true;
	  } else {
		  return false;
	  }
  },
  onEdit: function() { this._editContents(); },
  _editContents: function() {
	      var notebook = this.get('#notebook_textarea');
	      var contents = this.get('#notebook_div');
	      var notebook_dbl_click_message = this.get('#notebook_dbl_click_message');
	      var notebook_save_message = this.get('#notebook_save_message');
	      contents.css('display', 'none');
	      notebook.css('display', 'block');
	      notebook_dbl_click_message.css('display', 'none');
	      notebook_save_message.css('display', 'block');
	      notebook.focus();
  },
  _saveNote: function() {
	  var notect = this.get('#notebook_textarea').val();
	  this.ajaxRequest('saveData', [ notect ], function() { this.refresh(); } ); 
  }
}