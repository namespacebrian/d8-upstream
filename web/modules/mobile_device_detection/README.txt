CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Usage
 * Maintainers

INTRODUCTION
------------

"Mobile device detection" module can detect any mobile device. You can use it 
via service or "Views". This module integrate with "Views" and you can easily 
to switch "Views display" for different devices.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/mobile_device_detection

REQUIREMENTS
------------

 -No special requirements.

INSTALLATION
------------

 * Module: Install as you would normally install a contributed Drupal module.
   See: https://www.drupal.org/documentation/install/modules-themes/modules-8
   for further information.

CONFIGURATION
-------------

The module has no menu or modifiable settings. There is no configuration.

USAGE
-------------

	Initialization

	$detection_service = \Drupal::service('mobile_device_detection.object');

	You can to use a couple of methods to check devices

	if($detection_service->isMobile()){
		If is mobile then you can get object

    $detection_service->getObject();
  }

  if($detection_service->isTablet()){
  	If is tablet then you can get object

    $detection_service->getObject();
  }

MAINTAINERS
-----------

Current maintainers:
 * Victor Isaikin - https://www.drupal.org/u/depthinteractive
 * Site - https://depthinteractive.ru
