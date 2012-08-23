<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
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
	  return $this->VTAPP_windowTop;
	}
	
	// Get window left position
	public function getLeft() {
	  return $this->VTAPP_windowLeft;
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
		return $this->VTAPP_onScreen;
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