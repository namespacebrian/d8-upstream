Drupal 8 Buckeye Alerts
=======================

This module adds [BuckeyeAlerts](code.osu.edu/ucom/buckeye-alert) to the top of your Drupal 8 pages. The JavaScript and CSS was pulled directly from the BuckeyeAlert code.


Inclusion via Composer
------------

To add this to your composer project, you'll need to add a reference in the
repositories section and then include the project under require.

```
{
  "repositories": [
    {
      "type": "git",
      "url": "git@code.osu.edu:fcob-internal/buckeye-alert.git"
    }
  ],
  "require": {
    "fcob-internal/buckeye_alert": "master",
  }
}
```

Installation
------------

  * Add the module code into your `modules` folder.  This can be done via `composer` using the `repositories` section of `composer.json` ([documentation](https://getcomposer.org/doc/05-repositories.md))
  * Add the following to your theme's info.yml
    ```
    libraries:
      - buckeye_alert/buckeye_alert
    ```

Usage Notes
-----------

The settings page can be found under configuration->system->buckeye alerts. From there you will be able to manage the following settings:

  * **Use test Buckeye Alerts** -- Determines whether or not to use the testing Buckeye Alerts feed. Defaults to off.
  * **Alert feed URL** -- The RSS feed to check for alert messages.
  * **Container class** -- Optional classes to add to message container.
  * **Animate** -- Enables jQuery animations.
  * **Add styles** -- Include the recommended alert message styles.
  * **Responsive styles** -- Use the included responsive style sheet.
  * **Breakpoints** -- Responsive CSS tablet and phone breakpoints.

Add the "Administer Buckeye Alerts" permission to any necessary roles. 

