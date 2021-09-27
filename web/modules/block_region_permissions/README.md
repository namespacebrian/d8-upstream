CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Recommended Modules
* Installation
* Configuration
* Troubleshooting
* Maintainers


INTRODUCTION
------------

The Block Region Permissions module adds permissions for administering "blocks"
based on each theme's regions. The "Administer blocks" permission normally
manages this entity on the "Block layout" page.

* For additional information on the module visit:
  https://www.drupal.org/project/block_region_permissions

* To submit bug reports, suggest features, or track changes visit:
  https://www.drupal.org/project/issues/block_region_permissions


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


RECOMMENDED MODULES
-------------------

* Block Content Permissions:
  Adds permissions for administering the "Custom block library" pages.
  https://www.drupal.org/project/block_content_permissions


INSTALLATION
------------

Install this module as you would normally install a contributed Drupal module.
Visit https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configure the user permissions in `Administration » People » Permissions`:

  - Block - Administer blocks

    (Required) Allows management of blocks. **Warning:** This permission grants
    access to block pages not managed by this module. Use the recommended
    modules to restrict the rest.

  - Block Region Permissions - Administer: [*theme*] - [*region*]

    (Optional) For a specific theme's region: view it on the layout page,
    management its blocks, and select it in the region fields.

  - Contextual Links - Use contextual links

    (Optional) Allows use of operational links near the rendered blocks. This
    module hides links accordingly.

  - Quick Edit - Access in-place editing

    (Optional) Allows use of "in-place" editing near the rendered blocks.
    Requires the "Use contextual links" permission. This module restricts access
    and hides the link accordingly.

  - System - Use the administration pages and help

    (Optional) Allows use of admin pages during navigation.

  - System - View the administration theme

    (Optional) Allows use of administrative theme for aesthetics.

  - Toolbar - Use the toolbar

    (Optional) Allows use of toolbar during navigation.


TROUBLESHOOTING
---------------

List of pages that should deny access depending on permissions.

In-place **Quick edit**.

**"Block layout" pages:**
* **Block layout:**
  - **Path:** /admin/structure/block
  - **Route:** block.admin_display
  - **Path:** /admin/structure/block/list/{theme}
  - **Route:** block.admin_display_theme
* **Configure block:**
  - **Path:** /admin/structure/block/manage/{block}
  - **Route:** entity.block.edit_form
* **Delete block:**
  - **Path:** /admin/structure/block/manage/{block}/delete
  - **Route:** entity.block.delete_form
* **Disable block:**
  - **Path:** /admin/structure/block/manage/{block}/disable
  - **Route:** entity.block.disable
* **Enable block:**
  - **Path:** /admin/structure/block/manage/{block}/enable
  - **Route:** entity.block.enable
* **Place block search:**
  - **Path:** /admin/structure/block/library/{theme}
  - **Route:** block.admin_library
* **Place block configure:**
  - **Path:** /admin/structure/block/add/{plugin_id}/{theme}
  - **Route:** block.admin_add


MAINTAINERS
-----------

Current maintainers:
* Joshua Roberson - https://www.drupal.org/u/joshuaroberson
