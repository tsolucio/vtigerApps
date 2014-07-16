{
  onLoad: function() {
    setInterval($.proxy(this._changeIcon, this), 5000);
    this.addListener('com.tsolucio.AboutUs', this._message);
  },
  onRefresh: function() {
    this.get('#image-button').click($.proxy(this._changeIcon, this));
    this.get('#sendmsg').click($.proxy(function() { this.sendMessage(this.get('#msg').val()); }, this));
  },
  _message: function(msg) {
	  msgfrom = msg.slice(0,this.launcher.key.length);
	  msgtext = msg.slice(this.launcher.key.length+1);
	  this.get('#msg-content').html('Received message "<b>'+msgtext+'</b>" from '+msgfrom);
  },
  onResize: function() {
    this.get('#content-resize').html('<br>'+this.width+'<br>'+this.height);
  },
  _alternateIcon: false,
  _changeIcon: function() {
    this._alternateIcon = !this._alternateIcon;
    var launcherIcon = this.launcher.getWidget().find('img');
    if (this._alternateIcon) {
      launcherIcon.attr('src', this.getPath('evolutivo.png'));
    }
    else {
      launcherIcon.attr('src', this.getPath('icon.png'));
    }
  }
}