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
  private $canhideapp;
  private $canshowapp;
  private $windowDefaultWidth;
  private $windowDefaultHeight;
  private $jsFiles = array();
  private $cssFiles = array();
  private $translations;
  private $jsTranslations;
  
  // Create launcher
  public function __construct($id, $path) {
  	global $current_language,$currentModule,$current_user,$adb,$log;
		$this->id = $id;
		if (preg_match('/\/$/', $path)) {
		  $path = substr($path, 0, -1);
		}
		$this->path = $path;
		// Default values
		$defaults = array(
		  'icon_file' => 'icon.png',
		  'editable' => 0,
		  'resizable' => 1,
		  'clonable' => 0,
		  'visible' => 0,
		  'canhide' => 1,
		  'canshow' => 1
		  );
		// Read info from ini file
		$data = parse_ini_file($this->getPath('vtapp.ini'));
		$data = array_merge($defaults, $data);
		$this->key = $data['key'];
		$this->name = $data['name'];
		$this->shortDescription = $data['short_description'];
		$this->longDescription = $data['long_description'];
		$this->iconFile = $data['icon_file'];
		$this->className = $data['class_name'];
		$uid = $current_user->id;
		$rsapp=$adb->query("select * from vtiger_evvtappsuser where appid=$id and userid=$uid");
		$appinfo = $adb->fetch_array($rsapp);
		$this->editable = ($data['editable'] and $appinfo['canwrite'] ? 1 : 0);
		$this->resizable = $data['resizable'];
		$this->clonable = $data['clonable'];
		$this->visible = ($data['visible'] or $appinfo['wvisible'] ? 1 : 0);
		$this->canhideapp = ($data['canhide'] and $appinfo['canhide'] ? 1 : 0);
		$this->canshowapp = ($data['canshow'] and $appinfo['canshow'] ? 1 : 0);
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
      'canhide' => $this->canHide(),
      'canshow' => $this->canShow(),
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

  // App window can be hidden
  public function canHide() {
  	return $this->canhideapp;
  }

  // App window can be shown
  public function canShow() {
  	return $this->canshowapp;
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