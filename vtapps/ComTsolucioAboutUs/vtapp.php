<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/

class VtApp_ComTsolucioAboutUs extends vtAppBase {
	
	public function getContent() {
	  $about = '<div id="content" style="width:48%;float:left">';
		$about.= '<img id="image-button" src="'.$this->getLauncher()->getIconPath().'" style="float:left"><br/>';
		$about.= '<b>vtEvolutivo::vtApps</b><br/>';
		$about.= 'Copyright &copy; 2012<br/><br/>';
		$about.= 'Click on the icon to see my Canvas Icon change<br/><br/>';
		$about.= date('H:i:s').'<br/>';
		$about.= '<div id="content-resize"></div>';
		$about.= '<div id="sendm"><input type="text" id="msg" name="msg">&nbsp;<input type="button" id="sendmsg" value="'.$this->translate('sendmessage').'" class="crm small save"></div>';
		$about.= '<div id="msg-content"></div>';
		$about.= '</div>';
		$about.= '<div id="aboutus" style="width:48%;float:right">
		<table class="list" style="width: 100%%; float: right;">
		<thead>
		<tr>
		<th colspan="2">Maintainer</th>
		</tr>
		</thead>
		<tbody>
		<tr>
		<td style="padding-left: 15px;padding-right: 15px;">
		<a href="http://www.evolutivo.it">
		<img src="'.$this->getPath('evolutivo.png').'" alt="Evolutivo" title="Evolutivo" />
		</a>
		</td>
		<td>
		<b>vtApps</b> is an <b><a href="http://www.evolutivo.it">Evolutivo Initiative</a></b><br/>
		</td>
		</tr>
		<tr><td colspan=2>Which means it is a joint venture project of the companies:</td></tr>
		<tr><td colspan=2><b>OpenCubed. JPL TSolucio, S.L. and StudioSynthesis, S.R.L.</b></td></tr>
		<tr><td colspan=2><br/><br/>This vtApp informs about the creators of the vtApps framework and also serves as a demonstration of some of the features of the platform. We can see how the canvas icon is changed automatically every 5 seconds showing that it supports timed events in the browser. By clicking on the icon on the left we see how this vtApp is capable of communicating with the icon on the canvas. When the refresh button is clicked the time is recovered from the server and updated along with the rest of the contents of the window. If you resize the window the resize event is launched and prints the new size of the window inside the window and, finally, we show that any vtApp can send messages to another vtApp. In this case the message is sent to itself but it could be sent as easily to any other making it very easy for one vtApp to inform another to refresh it\'s contents in function of some change in the first one (for example).</td></tr>
		</tbody>
		</table>
		</div>';

		return $about;
	}
}
?>