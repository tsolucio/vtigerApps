<?php
/*************************************************************************************************
 * Copyright 2012 JPL TSolucio, S.L. -- This file is a part of evvtApps.
 * You can copy, adapt and distribute the work under the "Attribution-NonCommercial-ShareAlike"
 * Vizsage Public License (the "License"). You may not use this file except in compliance with the
 * License. Roughly speaking, non-commercial users may share and modify this code, but must give credit
 * and share improvements. However, for proper details please read the full License, available at
 * http://vizsage.com/license/Vizsage-License-BY-NC-SA.html and the handy reference for understanding
 * the full license at http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html. Unless required by
 * applicable law or agreed to in writing, any software distributed under the License is distributed
 * on an  "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the
 * License terms of Creative Commons Attribution-NonCommercial-ShareAlike 3.0 (the License).
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/

/**
 * This class is the parent of all apps and has all the base elements needed
 */
class VtAppBase {
  
  private $VTAPP_id;
  private $VTAPP_launcher;
  private $VTAPP_windowTop;
  private $VTAPP_windowLeft;
  private $VTAPP_windowWidth;
  private $VTAPP_windowHeight;
  private $VTAPP_onScreen;
  
	// Creates or loads an app instance for the given launcher
  public function __construct($launcher, $id) {
		$this->VTAPP_launcher = $launcher;
		$this->VTAPP_id = $id;
	}
	
	// Get data needed by the UI
	public function getUIData() {
	  $data = array(
	    'id' => $this->getId(),
	    'top' => $this->getTop(),
	    'left' => $this->getLeft(),
	    'width' => $this->getWidth(),
	    'height' => $this->getHeight(),
	  	'onscreen' => $this->getOnScreen(),
	    );
	  return $data;
  }
	
  // Get launcher for this app
  public function getLauncher() {
    return $this->VTAPP_launcher;
  }
  
  // Get all app properties, excludes framework properties (VTAPP_*)
	public function getProperties() {
	  $vars = get_object_vars($this);
	  foreach($vars as $k=>$v) {
	    if (substr($k, 0, 6)=='VTAPP_') {
	      unset($vars[$k]);
	    }
	  }
	  return $vars;
	}
	
	// Set app properties
	public function setProperties($data) {
	  foreach($data as $var=>$value) {
	    $this->$var = $value;
	  }
	}
	
	// Save app instance
	public function save() {
	  $vtAppManager = VtAppManager::getInstance();
	  $vtAppManager->saveAppInstance($this);
	}
	
	// Get path to the app
	public function getPath($path=NULL) {
    return $this->VTAPP_launcher->getPath($path);
  }
  
  // Get translation
	public function translate($str) {
		return $this->VTAPP_launcher->translate($str);
	}

	function getLanguageFilename() {
		return $this->VTAPP_launcher->getLanguageFilename();
	}

	// Move window
	public function moveWindow($top, $left) {
    $this->VTAPP_windowTop = $top;
    $this->VTAPP_windowLeft = $left;
  }
  
  // Resize window
  public function resizeWindow($width, $height) {
    $this->VTAPP_windowWidth = $width;
    $this->VTAPP_windowHeight = $height;
  }

  // Window on screen status
  public function windowOnScreen($onscreen) {
  	$this->VTAPP_onScreen = $onscreen;
  }

  // Get app instance id
  public function getId() {
	  return $this->VTAPP_id;
	}
	
	// Get app key
	public function getKey() {
	  return $this->VTAPP_launcher->getKey();
	}
	
	// Get app title
  public function getTitle() {
		return $this->translate('appName');
	}
	
	// Get app content
	public function getContent() {
	}
	
	// Get window top position
	public function getTop() {
	  return (empty($this->VTAPP_windowTop) ? 100 : $this->VTAPP_windowTop);
	}
	
	// Get window left position
	public function getLeft() {
	  return (empty($this->VTAPP_windowLeft) ? 100 : $this->VTAPP_windowLeft);
	}
	
	// Get window width
	public function getWidth() {
	  return $this->VTAPP_windowWidth;
	}
	
	// Get window height
	public function getHeight() {
	  return $this->VTAPP_windowHeight;
	}

	// Get window height
	public function getOnScreen() {
		return (is_null($this->VTAPP_onScreen) ? true : $this->VTAPP_onScreen);
	}

	// Get user object
	public function getUser() {
	  return VtAppManager::getInstance()->getUser();
	}
	
	public function postUpdate() {
	}

	public function postInstall() {
	}

	public function unInstall() {
	}
}
?>