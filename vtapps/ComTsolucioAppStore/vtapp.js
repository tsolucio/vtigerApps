{
  onRefresh: function() {
    this.get("#vtinstall").kendoUpload({
        async: {
          saveUrl: "index.php?"+evvtURLp+"&vtappaction=dovtAppMethod&vtappmethod=vtInstallApp&class=vtAppcomTSolucioAppStore&appid='.$this->appid.'",
          autoUpload: true
        }
    });
    this.get("#vtupdate").kendoUpload({
        async: {
          saveUrl: "index.php?"+evvtURLp+"&vtappaction=dovtAppMethod&vtappmethod=vtUpdateApp&class=vtAppcomTSolucioAppStore&appid='.$this->appid.'",
          autoUpload: true
        }
    });
  }
}