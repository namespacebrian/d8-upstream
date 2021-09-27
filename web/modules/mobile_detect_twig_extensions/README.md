CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Recommended modules
 * Installation
 * Configuration
 * Troubleshooting
 * FAQ
 * Maintainers


INTRODUCTION
------------

The Administration Menu module displays the entire administrative menu tree
(and most local tasks) in a drop-down menu, providing administrators one- or
two-click access to most pages.  Other modules may also add menu links to the
menu using hook_admin_menu_output_alter().

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/mobile_detect_twig_extensions

 * To submit bug reports and feature suggestions, or track changes:
   https://www.drupal.org/project/issues/mobile_detect_twig_extensions

REQUIREMENTS
------------

This module doesn't requires any modules.

RECOMMENDED MODULES
-------------------

 * Twig_tweak (https://www.drupal.org/project/twig_tweak):
   When enabled, you can test any extension. Useful for case estudies.

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/documentation/install/modules-themes/modules-8
   for further information.

 * Edit your twig templates and enjoy mobile_detect_twig_extensions


CONFIGURATION
-------------

The module has no menu or modifiable settings. There is no configuration. When
enabled, the module will prevent the links from appearing.

NOTE: It's a library interface, so it means just give support to different twig extensions:

Extensions allowed:

    'is_mobile' => new \Twig_Function_Function(array($this, 'isMobile')),
    'is_tablet' => new \Twig_Function_Function(array($this, 'isTablet')),
    'is_device' => new \Twig_Function_Function(array($this, 'isDevice')),
    'is_ios' => new \Twig_Function_Function(array($this, 'isIOS')),
    'is_android_os' => new \Twig_Function_Function(array($this, 'isAndroidOS')),

TROUBLESHOOTING
---------------

 * If the menu does not display, check the following:

   - Are the "Access administration menu" and "Use the administration pages
     and help" permissions enabled for the appropriate roles?

   - Does html.tpl.php of your theme output the $page_bottom variable?

FAQ
---

Q: I enabled "Aggregate and compress CSS files", but admin_menu.css is still
   there. Is this normal?

A: Yes, this is the intended behavior. the administration menu module only loads
   its stylesheet as needed (i.e., on page requests by logged-on, administrative
   users).

MAINTAINERS
-----------

Current maintainers:
 * Antonio Martínez (nonom) - https://www.drupal.org/user/54136

This project has been sponsored by:
 * nonomartinez.com
   Specialized in consulting and planning of Drupal powered sites, nonomartinez.com
   offers installation, development, theming, customization, and hosting
   to get you started. Visit https://www.nonomartinez.com for more information.
