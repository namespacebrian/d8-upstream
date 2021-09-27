<?php

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all envrionments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to ensure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * Place the config directory outside of the Drupal root.
 */
if (is_dir($_ENV['HOME'] . '/files/private/config')) {
  // error_log("Config sync dir: _ENV[HOME]/files/private/config exists, using this.");
  $config_sync_dir = $_ENV['HOME'] . '/files/private/config';
}
else if(!empty($_ENV['FILEMOUNT'])) {
  // error_log("Config sync dir: _ENV[FILEMOUNT] exists, using it.");
  $config_sync_dir = DRUPAL_ROOT . '/' . $_ENV['FILEMOUNT'] . '/private/config';
}
else {
  // error_log("Config sync dir: fell through to default.");
  $config_sync_dir = DRUPAL_ROOT . '/sites/default/files/private/config';
}
// error_log("Config sync dir: $config_sync_dir");

if(!is_dir($config_sync_dir)) {
  error_log("Config sync dir '$config_sync_dir' doesn't exist, attempting to create it.");
  if(mkdir($config_sync_dir, 0700, true)) {
    error_log("$config_sync_dir created");
  }
  else {
    error_log("Failed to create $config_sync_dir");
  }
}

$settings['config_sync_directory'] = $config_sync_dir;

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}

/**
 * Site-specific redirects
 */
$redirects_file = __DIR__ . "/redirects.php";
if (file_exists($redirects_file)) {
  include $redirects_file;
}

/**
 * Redirect www.*.osu.edu to *.osu.edu
 */
if (!$cli && preg_match('/^www\.(.+)\.osu\.edu$/', $_SERVER["HTTP_HOST"], $matches)) {
  $redirect_url = 'https://' . $matches[1] . ".osu.edu" . $_SERVER["REQUEST_URI"];
  
  if (extension_loaded('newrelic')) {
    newrelic_name_transaction("redirect");
  }

  $response_code = 302;
  header("HTTP/1.0 $response_code Moved");
  header("Location: $redirect_url");
  exit();
}

/**
 * Always install the 'standard' profile to stop the installer from
 * modifying settings.php.
 */
$settings['install_profile'] = 'minimal';
