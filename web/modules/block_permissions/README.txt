CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers

INTRODUCTION
------------
This module provides additional permissions for finer grained permissions in
managing blocks.

The following permissions are added:

- An administer blocks per enabled theme.
  This enables granting a client access to managing the blocks without them
  being able to change the blocks on the
  admin theme.

- Permissions per provider of block plugins.
  This enables granting a user permission to add certain types of blocks but not
  system block plugins.

REQUIREMENTS
------------

No requirements for this module.

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. See:
   https://drupal.org/documentation/install/modules-themes/modules-8
   for further information.

CONFIGURATION
-------------

After enabling this module users without the specific permissions will not be
able to manage the blocks, even if they have the permission to administer the
blocks.

Assign the permissions per theme and provider to each role you wish.

For Block layout page (/admin/structure/block) permission 'administer blocks' is not enough. User should be assigned
permission to administer block settings for the default theme also.


MAINTAINERS
-----------

Current maintainers:
 * Michiel Nugter - https://www.drupal.org/user/1023784
