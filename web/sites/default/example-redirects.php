<?php

if (php_sapi_name() != "cli") {
  $redirect_url = FALSE;
  $response_code = FALSE;

  // OSU redirects

	if (preg_match('/^\/~lewis.239(.*)$/', $_SERVER["REQUEST_URI"], $matches)) {
		$redirect_url = 'https://www.asc.ohio-state.edu/lewis.239';
		if (!empty($matches[1])) {
			$redirect_url .= $matches[1];
		}
	}
  // if($_SERVER['HTTP_HOST'] == 'chemistry.ohio-state.edu') {
  //   $response_code = 301;
  //   $redirect_url = "https://chemistry.osu.edu";
  // }
  // else if($_SERVER["REQUEST_URI"] == '/about/faculty-staff/faculty/awards') {
  //   $redirect_url = 'https://excelsior.biosci.ohio-state.edu/~carlson/history';
  //   $response_code = 302;
  // }

  if ($redirect_url) {
    if(!$response_code) {
      $response_code = 302;
    }
    
    # Name transaction "redirect" in New Relic for improved reporting (optional)
    if (extension_loaded('newrelic')) {
      newrelic_name_transaction("redirect");
    }

    header("HTTP/1.0 $response_code Moved");
    header("Location: $redirect_url");
    exit();
  }
}

// Require HTTPS.
if (isset($_SERVER['PANTHEON_ENVIRONMENT']) &&
   ($_SERVER['HTTPS'] === 'OFF') &&
   // Check if Drupal or WordPress is running via command line
   (php_sapi_name() != "cli")) {
  if (!isset($_SERVER['HTTP_USER_AGENT_HTTPS']) ||
      (isset($_SERVER['HTTP_USER_AGENT_HTTPS']) && $_SERVER['HTTP_USER_AGENT_HTTPS'] != 'ON')
      ) {

    # Name transaction "redirect" in New Relic for improved reporting (optional)
    if (extension_loaded('newrelic')) {
      newrelic_name_transaction("redirect");
    }

    header('HTTP/1.0 301 Moved Permanently');
    header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
  }
}
