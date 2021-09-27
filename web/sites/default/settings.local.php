<?php

// Created at: 2018-02-08 13:07:15

/**
 * Added by Pantheon Migration Tool.
 * Includes Pantheon-specific configs.
 */

/**
 * Helpers
 */
$cli = (php_sapi_name() === 'cli');

if(!isset($variables)) {
  $variables = array();
}

/**
 * Pantheon-specific settings
 */
if (!defined('PANTHEON_ENVIRONMENT')) {
    /**
     * Database settings:
     *
     * The $databases array specifies the database connection or
     * connections that Drupal may use.    Drupal is able to connect
     * to multiple databases, including multiple types of databases,
     * during the same request.
     *
     * One example of the simplest connection array is shown below. To use the
     * sample settings, copy and uncomment the code below between the @code and
     * @endcode lines and paste it after the $databases declaration. You will need
     * to replace the database username and password and possibly the host and port
     * with the appropriate credentials for your database system.
     *
     * The next section describes how to customize the $databases array for more
     * specific needs.
     *
     * @code
     * $databases['default']['default'] = array (
     *     'database' => 'databasename',
     *     'username' => 'sqlusername',
     *     'password' => 'sqlpassword',
     *     'host' => 'localhost',
     *     'port' => '3306',
     *     'driver' => 'mysql',
     *     'prefix' => '',
     *     'collation' => 'utf8mb4_general_ci',
     * );
     * @endcode
     */
     $databases['default']['default'] = array (
         'database' => 'd8demo',
         'username' => 'd8demo_user',
         'password' => 'd8demo_pass',
         'host' => 'localhost',
         'port' => '3306',
         'driver' => 'mysql',
         'prefix' => '',
         'collation' => 'utf8mb4_general_ci',
     );

     $settings['hash_salt'] = 'lol';
     $settings['pantheon_binding'] = 'localdev';

     $pressflow_settings = array(
         "databases" => $databases,
         "conf" => $settings,
     );
     $_SERVER['PRESSFLOW_SETTINGS'] = json_encode($pressflow_settings);


     $simplesamlphp_dir = DRUPAL_ROOT . "/../vendor/simplesamlphp/simplesamlphp/lib/_autoload.php";
     $settings['simplesamlphp_dir'] = $simplesamlphp_dir;
     require_once($simplesamlphp_dir);
     $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
}
else if (PANTHEON_ENVIRONMENT == 'lando') {
    $settings['skip_permissions_hardening'] = TRUE;
    
    # Force SSL
    if(isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == '80') {
        header('HTTP/1.0 301 Moved Permanently');
        header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    }

    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
    ini_set('html_errors', TRUE);
    $conf['error_level'] = 2;
    $settings['error_level'] = 2;

    if(!empty($_SERVER['LANDO_WEBROOT'])) {
        $_ENV['HOME'] = $_SERVER['LANDO_WEBROOT'];
        $_SERVER['HOME'] = $_SERVER['LANDO_WEBROOT'];
        //error_log('set home dir: ' . $_ENV['HOME']);
    }
    else {
        error_log("pantheon environment is lando but LANDO_WEBROOT isn't set");
    }

    $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
    $config['system.performance']['css']['preprocess'] = FALSE;
    $config['system.performance']['js']['preprocess'] = FALSE;
    $settings['cache']['bins']['render'] = 'cache.backend.null';
    $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
    $settings['extension_discovery_scan_tests'] = FALSE;

    # Migration database
    $databases['source_db']['default'] = array (
      'database' => 'pantheon',
      'username' => 'pantheon',
      'password' => 'pantheon',
      'host' => 'database.migrationsource.internal',
      'port' => '3306',
      'driver' => 'mysql',
      'prefix' => '',
      'collation' => 'utf8mb4_general_ci',
    );    
}
else if ( $_SERVER['HTTP_HOST'] == 'ascbsc.osu.edu' ||
            $_SERVER['HTTP_HOST'] == 'intranet.asc.ohio-state.edu' ||
            $_SERVER['HTTP_HOST'] == 'dev.ccapp.osu.edu' ||
            $_SERVER['HTTP_HOST'] == 'www.ascintranet.osu.edu') {
  $variables['domains'] = array (
    'canonical' => 'ascintranet.osu.edu',
    'additional' => array (
      0 => 'ascbsc.osu.edu',
      1 => 'intranet.asc.ohio-state.edu',
      2 => 'www.ascintranet.osu.edu',
    ),
  );
}
$variables['https'] = true;

// If necessary, force redirect in to https
if (isset($variables)) {
  if (array_key_exists('https', $variables) && $variables['https']) {
    if (!$cli && $_SERVER['HTTPS'] === 'OFF') {
      if (!isset($_SERVER['HTTP_X_SSL']) || (isset($_SERVER['HTTP_X_SSL']) && $_SERVER['HTTP_X_SSL'] != 'ON')) {
        header('HTTP/1.0 301 Moved Permanently');
        header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
      }
    }
  }
}

if (array_key_exists('redis', $variables) && $variables['redis']) {
  // Set possible redis module paths.
  $redis_paths = array(
    implode(DIRECTORY_SEPARATOR, array('sites', 'default', 'modules', 'contrib', 'redis')),
    implode(DIRECTORY_SEPARATOR, array('sites', 'default', 'modules', 'redis')),
    implode(DIRECTORY_SEPARATOR, array('modules', 'contrib', 'redis')),
    implode(DIRECTORY_SEPARATOR, array('modules', 'redis')),
  );

  if (array_key_exists('CACHE_HOST', $_ENV) && !empty($_ENV['CACHE_HOST'])) {
    foreach ($redis_paths as $path) {
      if (is_dir($path)) {
        if (in_array('example.services.yml', scandir($path))) {
          $settings['container_yamls'][] = $path . DIRECTORY_SEPARATOR . 'example.services.yml';

          $settings['redis.connection']['interface'] = 'PhpRedis';
          $settings['redis.connection']['host'] = $_ENV['CACHE_HOST'];
          $settings['redis.connection']['port'] = $_ENV['CACHE_PORT'];
          $settings['redis.connection']['password'] = $_ENV['CACHE_PASSWORD'];

          $settings['cache']['default'] = 'cache.backend.redis';
          $settings['cache_prefix']['default'] = 'pantheon-redis';

          $settings['cache']['bins']['bootstrap'] = 'cache.backend.chainedfast';
          $settings['cache']['bins']['discovery'] = 'cache.backend.chainedfast';
          $settings['cache']['bins']['config'] = 'cache.backend.chainedfast';

          break;
        }
      }
    }
  }
}

if (PANTHEON_ENVIRONMENT != 'live') {
  // Place for settings for the non-live environment
}

if (PANTHEON_ENVIRONMENT == 'dev') {
  // Place for settings for the dev environment
}

if (PANTHEON_ENVIRONMENT == 'test') {
  // Place for settings for the test environment
}

if (PANTHEON_ENVIRONMENT == 'live') {
  // Place for settings for the live environment

  // Redirect to canonical domain
  if (isset($variables)) {
    if (isset($variables['domains']['canonical'])) {
      if (!$cli) {
        $location = false;

        // Get current protocol
        $protocol = 'http';

        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
          $protocol = 'https';
        }

        // Default redirect
        $redirect = "$protocol://{$variables['domains']['canonical']}{$_SERVER['REQUEST_URI']}";

        if ($_SERVER['HTTP_HOST'] == $variables['domains']['canonical']) {
          $redirect = false;
        }

        if (isset($variables['domains']['synonyms']) && is_array($variables['domains']['synonyms'])) {
          if (in_array($_SERVER['HTTP_HOST'], $variables['domains']['synonyms'])) {
            $redirect = false;
          }
        }

        if ($redirect) {
          header("HTTP/1.0 301 Moved Permanently");
          header("Location: $redirect");
          exit();
        }
      }
    }
  }
}

foreach (array('dev', 'test', 'live') as $environment) {
  if (isset($variables['environments'][$environment]['conf']) && is_array($variables['environments'][$environment]['conf'])) {
    foreach ($variables['environments'][$environment]['conf'] as $variable => $value) {
      $conf[$variable] = $value;
    }
  }

  if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . "files/private/settings/$environment.settings.php")) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . "files/private/settings/$environment.settings.php";
  }
}
