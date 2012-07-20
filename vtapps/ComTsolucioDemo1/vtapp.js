{
  onLoad: function() {
    setInterval($.proxy(this._changeIcon, this), 5000);
  },
  onRefresh: function() {
    //this.get('#image-button').click($.proxy(this._changeIcon, this));
    this.get('#image-button').click($.proxy(function() { this.sendMessage('doSomething'); }, this));
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