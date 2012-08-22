<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/

/**
 * This class is an abstraction of the app. It handles properties common to all instances of an app.
 */
class VtAppLauncher {
  private $id;
  private $path;
  private $key;
  private $iconFile;
  private $className;
  private $name;
  private $shortDescription;
  private $longDescription;
  private $editable;
  private $resizable;
  private $clonable;
  private $visible;
  private $windowDefaultWidth;
  private $windowDefaultHeight;
  private $jsFiles = array();
  private $cssFiles = array();
  private $translations;
  private $jsTranslations;
  
  // Create launcher
  public function __construct($id, $path) {
		$this->id = $id;
		if (preg_match('/\/$/', $path)) {
		  $path = substr($path, 0, -1);
		}
		$this->path = $path;
		// Default values
		$defaults = array(
		  'icon_file' => 'icon.png',
		  );
		// Read info from ini file
		$data = parse_ini_file($this->getPath('vtapp.ini'));
		$data = array_merge($data, $defaults);
		$this->key = $data['key'];
		$this->name = $data['name'];
		$this->shortDescription = $data['short_description'];
		$this->longDescription = $data['long_description'];
		$this->iconFile = $data['icon_file'];
		$this->className = $data['class_name'];
		$this->editable = $data['editable'];
		$this->resizable = $data['resizable'];
		$this->clonable = $data['clonable'];
		$this->visible = $data['visible'];
		$this->windowDefaultWidth = $data['window_default_width'];
		$this->windowDefaultHeight = $data['window_default_height'];
		if (isset($data['js_files'])) {
		  $this->jsFiles = explode(',', $data['js_files']);
		  foreach($this->jsFiles as &$jsFile) {
		    $jsFile = $this->getPath(trim($jsFile));
		  }
		}
		if (isset($data['css_files'])) {
		  $this->cssFiles = explode(',', $data['css_files']);
		  foreach($this->cssFiles as &$cssFile) {
		    $cssFile = $this->getPath(trim($cssFile));
		  }
		}
		if (file_exists($this->getPath('vtapp.css'))) {
		    array_push($this->cssFiles, $this->getPath('vtapp.css'));
		}
		// Translations
		// First get base module translations
		global $current_language,$currentModule;
		include "modules/{$currentModule}/language/{$current_language}.lang.php";
		if (!empty($mod_strings) and is_array($mod_strings)) {
			$this->translations = $mod_strings;
		} else {
			$this->translations = array();
		}
		if (!empty($vtapps_js) and is_array($vtapps_js)) {
			$this->jsTranslations = $vtapps_js;
		} else {
			$this->jsTranslations = array();
		}
		// Now mix with the vtApp strings
		$languageFilename = $this->getLanguageFilename();
		if (!is_null($languageFilename)) {
		  include($languageFilename);
		  if (!empty($vtapps_strings) and is_array($vtapps_strings)) {
		  	$this->translations = array_merge($this->translations,$vtapps_strings);
		  }
		  if (!empty($vtapps_js_strings) and is_array($vtapps_js_strings)) {
		  	$this->jsTranslations = array_merge($this->jsTranslations,$vtapps_js_strings);
		  }
		}
		// Load class
		require_once($this->getPath('vtapp.php'));
  }
  
  function getLanguageFilename() {
    $vtAppManager = VtAppManager::getInstance();
		$language = $vtAppManager->getLanguage();
		$filename = $this->getPath("language/{$language}.lang.php");
		if (!file_exists($filename)) {
		  $filename = $this->getPath("language/en_us.lang.php");
		  if (!file_exists($filename)) {
		    return null;
		  }
		}
		return $filename;
  }
  
  // Get UI data for all instances of this launcher's app
  public function getUIData() {
    $data = array(
      'id' => $this->getId(),
      'name' => $this->getName(),
      'path' => $this->getPath(),
      'key' => $this->getKey(),
      'iconPath' => $this->getIconPath(),
      'editable' => $this->isEditable(),
      'resizable' => $this->isResizable(),
      'clonable' => $this->isClonable(),
      'visible' => $this->isVisible(),
      'shortDescription' => $this->getShortDescription(),
      'longDescription' => $this->getLongDescription(),
      'jsFiles' => $this->getJSFiles(),
      'cssFiles' => $this->getCSSFiles(),
      'handlers' => $this->getHandlers(),
      'translations' => $this->jsTranslations,
      );
    return $data;
  }
  
  // Get app/launcher id
  public function getId() {
    return $this->id;
  }
  
  // Get app/launcher key
  public function getKey() {
    return $this->key;
  }
  
  // Get class name
  public function getClassName() {
    return $this->className;
  }
  
  // Get app path
  public function getPath($path=NULL) {
    if (!preg_match('/^\//', $path)) {
      $path = '/'.$path;
    }
    return $this->path.$path;
  }
  
  // Get translation
  public function translate($str) {
		$trstr = $this->translations[$str];
		if (empty($trstr)) {
		  $trstr = $str;
		}
		return $trstr;
	}
	
	// Get javascript code for the app
	public function getHandlers() {
	  $jsfile = $this->getPath('vtapp.js');
	  if (!file_exists($jsfile)) {
	    return '';
	  }
	  return file_get_contents($jsfile);
	}
	
	// Get javascript files
	public function getJSFiles() {
	  return $this->jsFiles;
	}
	
	// Get CSS
	public function getCSSFiles() {
	  return $this->cssFiles;
	}
	
	// Get icon path
	public function getIconPath() {
	  return $this->getPath($this->iconFile);
	}
	
	// Get app name
	public function getName() {
    return $this->translate($this->name);
  }
  
  // App has editor (config window)
  public function isEditable() {
    return $this->editable;
  }
  
  // App window is resizable
  public function isResizable() {
    return $this->resizable;
  }
  
  // App window is clonable
  public function isClonable() {
    return $this->clonable;
  }
  
  // App window is visible
  public function isVisible() {
    return $this->visible;
  }
  
  // Get short description for the app
  public function getShortDescription() {
    return $this->translate($this->shortDescription);
  }
  
  // Get long description for the app
  public function getLongDescription() {
    return $this->translate($this->longDescription);
  }
  
  // Get app window default width
  public function getWindowDefaultWidth() {
    return $this->windowDefaultWidth;
  }
  
  // Get app window default height
  public function getWindowDefaultHeight() {
    return $this->windowDefaultHeight;
  }
  
}
?>