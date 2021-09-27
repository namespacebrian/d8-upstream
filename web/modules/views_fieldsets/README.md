CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

Creates fieldset (and details and div) in Views fields output, to group fields,
by adding a new field: "Global: Fieldset" and a few preprocessors. Also
introduces a new template: views-fieldsets-fieldset.tpl.php where you can
customize your fieldset output.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/views_fieldsets

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/views_fieldsets


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


INSTALLATION
------------

 * Install the Views fieldsets module as you would normally install a
   contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

    1. Navigate to Administration > Extend and enable the module.
    2. Navigate to Administration > Structure > views and open existing view or
       a create a new view.
    3. Add some fields.
    4. Add field "Global: Fieldset" and customize settings (html tag,
       collapsible, tokens etc)
    5. Rearrange fields to drag normal fields under Fieldset fields. You can
       nest fieldsets. The result will be visible in Preview.

Theming:
There are several new templates. You can specify the filename the Views way. See
Theme: Information for theme hook suggestion specifics. Available:

 * views-fieldsets-fieldset.tpl.php
 * views-fieldsets-fieldset--events.tpl.php
 * views-fieldsets-fieldset--default.tpl.php (all tags)
 * views-fieldsets-fieldset--default.tpl.php (per tag)
 * views-fieldsets-fieldset--page.tpl.php
 * views-fieldsets-fieldset--events--page.tpl.php
 
And of course the related preprocessors:

```
template_preprocess_views_fieldsets_fieldset(),
template_preprocess_views_fieldsets_fieldset__events() etc.
```


MAINTAINERS
-----------

 * rudiedirkx - https://www.drupal.org/u/rudiedirkx

Supporting organizations:

 * GOLEMS GABB - https://www.drupal.org/golems-gabb
