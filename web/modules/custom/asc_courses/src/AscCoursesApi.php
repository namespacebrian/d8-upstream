<?php
namespace Drupal\asc_courses;

// use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
// use Drush\Commands\DrushCommands;
use \Drupal\Core\Database\Database;
use \Drupal\Core\Config;

/**
 * Fetch, store, and process data from EIP API
 */
class AscCoursesApi {
  public $debug = 0;
  public $consumer_key;
  public $consumer_secret;
  public $config;
  public $soip_constant_name;
  public $environment;
  public $base_url;
  public $access_token;
  public $access_token_data;
  public $resolve_host;
  public $db;
  public $ch;


  /**
   * {@inheritdoc}
   */
  public function __construct($config = null, $db = null, $environment = '') {
    if ($this->debug) echo "\n============ AscCoursesApi::__construct() ============\n";

    // Get config if not passed
    if (!empty($config)) {
      $this->config = $config;
    }
    else {
      $this->config = $config = \Drupal::service('config.factory')->get('asc_courses.settings');
    }

    // Get environment if not passed
    if(empty($environment)) {
      if ($this->debug) echo "Environment argument was empty. Reading it from configuration.\n";
      $environment = $this->config->get('asc_courses.eip_environment');
    }
    if ($this->debug) echo "Environment: " . $environment . "\n";

    // Get database connection
    if(empty($db)) {
      $this->db = \Drupal::database();
    }
    else {
      $this->db = $db;
    }

    // HARD CODED VALUES - should these be defined elsewhere?
    if ($environment == 'prod') {
      $this->environment = $environment;
      $this->base_url = "https://apig.eip.osu.edu/";
      $this->consumer_key = $config->get('asc_courses.prod_consumer_key');
      $this->consumer_secret = $config->get('asc_courses.prod_consumer_secret');
      $this->soip_constant_name = "PANTHEON_SOIP_EIP_PROD";
    }
    else {
      $this->environment = $environment;
      $this->base_url = "https://apig-qa.eip.osu.edu/";
      $this->consumer_key = $config->get('asc_courses.qa_consumer_key');
      $this->consumer_secret = $config->get('asc_courses.qa_consumer_secret');
      $this->soip_constant_name = "PANTHEON_SOIP_EIP";
    }

    // (Legacy) Pantheon "Secure Integration" support - establish "resolve host"
    if(isset($_ENV['PANTHEON_ENVIRONMENT']) && $_ENV['PANTHEON_ENVIRONMENT'] != "lando") {
      if ($this->debug) echo "Pantheon environment: " . $_ENV['PANTHEON_ENVIRONMENT'] . "\n";
      $host = parse_url($this->base_url, PHP_URL_HOST);
      if ($this->debug) echo "host: $host\n";
      $localhost = "127.0.0.1";
      $this->resolve_host = array(sprintf("%s:%d:%s", $host, constant($this->soip_constant_name), $localhost));
      if ($this->debug) echo "\n resolve_host : " . implode($this->resolve_host) . "\n";
      // error_log("resolve_host : " . implode($this->resolve_host));
    }

    if ($this->debug) echo "\n\n";
  }


  /**
   * {@inheritdoc}
   */
  public function __destruct() {
    if(!empty($this->ch)) {
      curl_close($this->ch);
    }
  }


  /**
   * Return an access token. Fetch a new one if necessary.
   */
  public function getAccessToken() {
    $now = time();

    if($this->debug) echo "============ AscCoursesApi::getAccessToken() =============\n";
    if($this->debug) echo "Current time: " . date("Y-m-d h:i:s", $now) . "\n";


    // Treat access tokens that are almost expired as expired
    if(!empty($this->access_token_data)
       && (($this->access_token_data->expiration - $now) > 30))
    {
      // First check object variables
      if($this->debug) echo "Object variables were set and not stale\n";
      $this->access_token = $this->access_token_data->access_token;
    }
    else {
      // Object variables stale or missing, fall back on stored config
      $env_access_token_setting = 'asc_courses.' . $this->environment . '_access_token';
      if($this->debug) echo "env_access_token_setting: $env_access_token_setting\n";
      $access_token_data = unserialize($this->config->get($env_access_token_setting));
      if($this->debug) echo "Config access token: " . print_r($access_token_data, true) . "\n";

      if(!empty($access_token_data)
         && (($access_token_data->expiration - $now) > 30))
      {
        if($this->debug) echo "Config token was available and not stale.\n";
        $this->access_token_data = $access_token_data;
        $this->access_token = $access_token_data->access_token;
      }
      else {
        if($this->debug) echo "Config token was unset or stale.\n";

        // Fetch new access token from EIP
        $access_token_data = $this->fetchAccessToken();

        if(!empty($access_token_data->access_token)) {
          $this->access_token_data = $access_token_data;
          $this->access_token = $access_token_data->access_token;
        }
      }
    }

    if (!empty($this->access_token_data)) {
      if($this->debug) {
        $expirey = $this->access_token_data->expiration;
        echo "Token expiration: " . date("Y-m-d h:i:s", $expirey) . "\n";
        echo "Expires in " . ($expirey - $now) . " seconds..\n";
        echo "\n\n";
      }
      return $this->access_token;
    }
    else {
      die("\nAscCoursesApi::getAccessToken() - failed to retrieve access token! X_X\n\n");
    }
  }

  /**
   * Fetch a new access token via the API and return it
   */
  public function fetchAccessToken() {
    if($this->debug) echo "============ AscCoursesApi::fetchAccessToken() =============\n";

    // get an access token
    $bearer_auth_plain = $this->consumer_key . ":" . $this->consumer_secret;
    if ($this->debug) echo "AscCoursesApi::fetchAccessToken() - Tokens: " . $bearer_auth_plain . "\n";
    $bearer_auth = base64_encode($bearer_auth_plain);
    if ($this->debug) echo "AscCoursesApi::fetchAccessToken() - Bearer auth: $bearer_auth\n";

    $access_token_url = $this->base_url . "token?grant_type=client_credentials";
    if ($this->debug) echo "AscCoursesApi::fetchAccessToken() - Access token URL: $access_token_url\n";

    /*
      // get access token -- old drupal-ish way
      $token_response = \Drupal::httpClient()
        ->post($access_token_url,
            [
              'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Basic $bearer_auth"
              ],
              'form_params' => [
                'grant_type' => 'client_credentials'
              ]
            ]
      );
      $json_string = (string) $token_response->getBody();
      $json_data = json_decode($json_string);
      print_r($json_data);
      $access_token = json_decode($token_response->getBody())->access_token;
      // echo "access_token: $access_token\n";
    */

    // Pantheon "Secure Integration" compatible raw curl way
    $access_token_headers = [
      "Accept: application/json",
      "Authorization: Basic $bearer_auth",
    ];

    if(empty($this->ch)) {
      $this->ch = curl_init();
    }
    curl_setopt($this->ch, CURLOPT_URL, $access_token_url);

    curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($this->ch, CURLOPT_POST, true);
    // curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($this->ch, CURLOPT_HTTPHEADER, $access_token_headers);
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);

    if(isset($this->resolve_host)) {
      if ($this->debug) echo "AscCoursesApi::fetchAccessToken() - Resolve host set: " . print_r($this->resolve_host, true) . "\n";
      curl_setopt($this->ch, CURLOPT_RESOLVE, $this->resolve_host);
      curl_setopt($this->ch, CURLOPT_PORT, constant($this->soip_constant_name));
    }

    if($this->debug > 1) {
      curl_setopt($this->ch, CURLOPT_VERBOSE, true);
    }

    $access_token_start = microtime(true);
    $access_token_result = curl_exec($this->ch);
    $access_token_error = curl_error($this->ch);
    // curl_close($ch);
    $access_token_finish = microtime(true);
    $access_token_seconds = $access_token_finish - $access_token_start;
    if ($this->debug) echo "AscCoursesApi::fetchAccessToken() - CURL error: " . print_r($access_token_error, true) . "\n";

    if(!empty($access_token_error) || empty($access_token_result)) {
      if ($this->debug) echo "\n$access_token_error\n";
      die("AscCoursesApi::fetchAccessToken() - Failed to fetch access token from the API!!  X_X\n\n");
    }
    else {
      $now = time();
      $access_token_data = json_decode($access_token_result);
      $access_token_data->expiration = (time() + $access_token_data->expires_in);
      $access_token_data->fetched = $now;

      if ($this->debug) {
        echo "fetchAccessToken() - Access token result: \n";
        print_r($access_token_result);
        echo "\n";
        echo "fetchAccessToken() - Access token in $access_token_seconds seconds\n";
        echo "fetchAccessToken() - Now: " . $now . " - " . date("Y-m-d h:i:s", $now) . "\n";
        print_r($access_token_data);
      }

      // Save access token data to config
      $env_access_token_setting = 'asc_courses.' . $this->environment . '_access_token';
      if($this->debug) echo "fetchAccessToken() - env_access_token_setting: $env_access_token_setting\n";
      $config = \Drupal::service('config.factory')->getEditable('asc_courses.settings');
      $config->set("asc_courses." . $this->environment . "_access_token", serialize($access_token_data));
      $config->save();

      return $access_token_data;
    }
  }

  /**
   * UNUSED? - Fetch and store courses for a single D-Org
   */
  public function fetchDorgCourses($dorg, $force = false) {
    if($this->debug) echo "============ AscCoursesApi::fetchDorgCourses() =============\n";

    if(!$force) {
      // if($this->debug) echo "=========================\n";
      $dorg_data = $this->db->select('asc_courses_api_data', 'acad')
        // ->fields('acad', ['asc_courses_api_data_id', 'date', 'dept_org', 'environment', 'request_type', 'processed'])
        ->fields('acad')
        ->condition('dept_org', $dorg, '=')
        ->orderBy('date', 'desc')
        ->range(0,1)
        ->execute()
        ->fetchAssoc();
      // echo "$dorg row: " . print_r($dorg_data, true) . "\n";
      // $data_no_raw = $dorg_data;
      // unset($data_no_raw['raw_json']);
      return $dorg_data;
    }

    $access_token = $this->getAccessToken();
    $course_data_url = $this->base_url . "crseinfo/1.0.0/getCatalogInfo?campus=COL&acad_org=$dorg";

    if($this->debug) echo "course data url: $course_data_url\n";
    $course_data_headers = [
      "Accept: application/json",
      "Authorization: Bearer $access_token"
    ];

    if(empty($this->ch)) {
      $this->ch = curl_init();
    }

    curl_setopt($this->ch, CURLOPT_URL, $course_data_url);

    curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 5);
    // curl_setopt($this->ch, CURLOPT_POST, true);
    // curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($this->ch, CURLOPT_HTTPHEADER, $course_data_headers);
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);

    if(isset($this->resolve_host)) {
      if ($this->debug) echo "fetchDorgCourses() - Resolve host set: " . $this->resolve_host . "\n";
      // error_log("fetchDorgCourses() - Resolve host set: " . $this->resolve_host);
      curl_setopt($this->ch, CURLOPT_RESOLVE, $this->resolve_host);
      curl_setopt($this->ch, CURLOPT_PORT, constant($this->soip_constant_name));
    }

    if($this->debug > 1) {
      curl_setopt($this->ch, CURLOPT_VERBOSE, true);
    }

    $curl_start = microtime(true);
    $course_data_result = curl_exec($this->ch);
    $curl_error = curl_error($this->ch);
    // curl_close($ch);
    $curl_finish = microtime(true);
    $curl_seconds = $curl_finish - $curl_start;
    if ($this->debug) echo "fetchDorgCourses() - CURL error: " . print_r($curl_error, true) . "\n";

    if(!empty($curl_error) || empty($course_data_result)) {
      echo "\n$curl_error\n";
      echo "fetchDorgCourses() - Failed to fetch [$dorg] course information from the API!!  X_X\n\n";
      return 0;
    }
    else {
      if ($this->debug) {
        echo "Course data length:" . strlen($course_data_result) . "\n";
        echo "Course data in $curl_seconds seconds\n\n";
      }
      else {
        echo " - " . strlen($course_data_result) . " characters in " . round($curl_seconds, 2) . " seconds.";
      }


      // $connection = \Drupal::database();
      $row = [
        'date' => time(),
        'environment' => $this->environment,
        'request_type' => 'crseinfo',
        'dept_org' => $dorg,
        'raw_json' => $course_data_result,
        'processed' => 0,
      ];
      $this->db->insert('asc_courses_api_data')->fields($row)->execute();

      $course_data = json_decode($course_data_result);
      return $course_data;
    }


  }

  /**
   * UNUSED? - Process one unprocessed `asc_courses_api_data` record for current environment and specified dept_orgs
   */
  public function processDorgCourses($dorg) {
    if($this->debug) echo "============ AscCoursesApi::processDorgCourses() =============\n";

    $dorg_query = $this->db->select("asc_courses_api_data", "acad")
        ->fields("acad")
        ->condition("acad.dept_org", $dorg, "=")
        ->condition("processed", "0", "=")
        ->condition("environment", $this->environment, "=");
    $dorg_result = $dorg_query->execute();
    if($row = $dorg_result->fetchAssoc()) {
      $this->processApiDorgCoursesRecord($row);
    }
    else {
      // No unprocessed records found for this environment/dept_org
      if ($this->debug) echo "processDorgCourses(): No unprocessed API data for environment[" . $this->environment . "] and dept_org[" . $dorg . "]\n";
      return false;
    }
  }

  /**
   * Process any remaining unprocessed api data records
   */
  public function processPendingApiData($environment = null, $count = null) {
    if($this->debug) echo "============ AscCoursesApi::processPendingApiData() =============\n";
    else echo "Processing pending API data ";

    $query = $this->db->select("asc_courses_api_data", "acad")
        ->fields("acad")
        ->condition("processed", "0", "=")
        ->orderBy('date', 'ASC');

    if(!empty($environment)) {
      $query->condition("environment", $environment, "=");
    }

    if(!empty($count) && is_numeric($count)) {
      if ($this->debug) echo "processPendingApiData(): Count argument specified: $count";
      $query->range(0, $count);
    }

    $api_data_result = $query->execute();
    while($row = $api_data_result->fetchAssoc()) {
      if($row['request_type'] == 'crseinfo') {

        $this->processApiDorgCoursesRecord($row);
      }
      else {
        die("AscCoursesApi::processPendingApiData(): unexpected asc_course_api_data row request_type: " . $row['request_type']);
      }
    }
    if(!$this->debug) echo " done!\n";
  }

  /**
   * Process the passed `asc_courses_api_data` row
   */
  public function processApiDorgCoursesRecord($row) {
    if ($this->debug) {
      echo "============ AscCoursesApi::processApiDorgCoursesRecord() =============\n";
      echo "asc_courses_api_data_id: " . $row["asc_courses_api_data_id"] . "\n";
      echo "dept_org: " . $row["dept_org"] . "\n";
      echo "date: " . $row["date"] . "\n";
      echo "environment: " . $row["environment"] . "\n";
      echo "request_type: " . $row["request_type"] . "\n";
      echo "processed: " . $row["processed"] . "\n";
      echo "json data length: " . strlen($row['raw_json']) . "\n";
    }
    else {
      echo '.';
    }

    $json_data = json_decode($row['raw_json']);
    if(!$json_data) {
      die("\nAscCoursesApi::processApiDorgCoursesRecord(): Something went wrong processing the raw_json\n\n");
    }

    foreach($json_data->getCourseCatalogResponse->catalog->course as $course_data) {
      if(empty($course_data->{'crse-id'})) {
        if ($this->debug) echo "This course has no ID >:( " . print_r($course_data, true) . "\n";
      }
      else {
        $course_data->asc_courses_api_data_id = $row["asc_courses_api_data_id"];
        $course_data->dept_org = $row["dept_org"];
        // print_r($course_data);
        $existing_row = $this->db->select("asc_courses_processed", "acp")
            ->fields("acp")
            ->condition("crse_id", $course_data->{'crse-id'})
            ->execute()
            ->fetchAssoc();

        if(!empty($existing_row)) {
          $this->updateProcessedCourse($existing_row, $course_data);
        }
        else {
          $this->insertProcessedCourse($course_data);
        }
      }
    }

    if ($this->debug) echo "=====================\n\n";

    $set_processed_flag = $this->db->update("asc_courses_api_data")
      ->fields(["processed" => 1])
      ->condition("asc_courses_api_data_id", $row["asc_courses_api_data_id"], "=")
      ->execute();
    if ($this->debug) echo "Update processed flag: $set_processed_flag\n";
  }

  /**
   * Insert a new course data record into `asc_courses_processed` table
   */
  public function insertProcessedCourse($course_data) {
    if ($this->debug) {
      echo "============ AscCoursesApi::insertProcessedCourse() =============\n";
      echo " - " . $course_data->{'crse-id'};
      echo " - " . $course_data->{'subject'};
      echo " - " . $course_data->{'catalog-nbr'};
      echo " - " . $course_data->{'descr'};
      echo "\n";
      // echo "course data: " . print_r($course_data, true) . "\n";
      // echo "course_title_long length: " . strlen($course_data->{'course-title-long'}) . "\n";
    }

    $row = [
      "updated" => time(),
      "asc_courses_api_data_id"   => $course_data->{'asc_courses_api_data_id'},
      "dept_org"                  => $course_data->{'dept_org'},
      "crse_id"                   => $course_data->{'crse-id'},
      "strm"                      => $course_data->{'strm'},
      "effdt"                     => $course_data->{'effdt'},
      "eff_status"                => $course_data->{'eff-status'},
      "descr"                     => $course_data->{'descr'},
      "descrlong"                 => $course_data->{'descrlong'},
      "crse_attribute"            => serialize($course_data->{'crse-attribute'}),
      "equiv_crse_id"             => $course_data->{'equiv-crse-id'},
      "allow_mult_enroll"         => $course_data->{'allow-mult-enroll'},
      "units_minimum"             => $course_data->{'units-minimum'},
      "units_maximum"             => $course_data->{'units-maximum'},
      "acad_prog"                 => $course_data->{'acad-prog'},
      "crse_repeatable"           => $course_data->{'crse-repeatable'},
      "units_repeat_limit"        => $course_data->{'units-repeat-limit'},
      "crse_repeat_limit"         => $course_data->{'crse-repeat-limit'},
      "grading_basis"             => $course_data->{'grading-basis'},
      "ssr_component"             => $course_data->{'ssr-component'},
      "course_title_long"         => $course_data->{'course-title-long'},
      "crse_count"                => $course_data->{'crse-count'},
      "component_primary"         => $course_data->{'component-primary'},
      "crse_offer_nbr"            => $course_data->{'crse-offer-nbr'},
      "acad_group"                => $course_data->{'acad-group'},
      "subject"                   => $course_data->{'subject'},
      "catalog_nbr"               => $course_data->{'catalog-nbr'},
      "campus"                    => $course_data->{'campus'},
      "acad_org"                  => $course_data->{'acad-org'},
      "acad_career"               => $course_data->{'acad-career'},
      "cip_code"                  => $course_data->{'cip-code'},
    ];
    // echo "course row: " . print_r($row, true) . "\n";

    $result = $this->db->insert('asc_courses_processed')->fields($row)->execute();
    if ($this->debug) echo "INSERT result: " . print_r($result, true) . "\n";
    if ($this->debug) echo "\n";
  }

  /**
   * Update existing record in `asc_courses_processed` table
   */
  public function updateProcessedCourse($existing_row, $new_course_data) {
    if ($this->debug) {
      echo "============ AscCoursesApi::updateProcessedCourse() =============\n";
      echo " - " . $new_course_data->{'crse-id'};
      echo " - " . $new_course_data->{'subject'};
      echo " - " . $new_course_data->{'catalog-nbr'};
      echo " - " . $new_course_data->{'descr'};
      echo "\n";

      // echo "existing_row: " . print_r($existing_row, true) . "\n";
      // echo "course data: " . print_r($new_course_data, true) . "\n";
    }

    $row_fields = array_keys($existing_row);

    // echo "row fields: " . print_r($row_fields, true) . "\n";

    $new_course_data->{"crse-attribute"} = serialize($new_course_data->{"crse-attribute"});

    $update_fields = [];
    foreach($row_fields as $field_name) {
      $obj_field_name = str_replace("_", "-", $field_name);
      if($existing_row[$field_name] != $new_course_data->{$obj_field_name}) {
        if(
             $field_name != 'asc_courses_processed_id'
          && $field_name != 'updated'
          && $field_name != 'asc_courses_api_data_id'
          && $field_name != 'dept_org'
          && $field_name != 'strm'
        ) {
          if ($this->debug) {
            echo $existing_row[$field_name];
            echo " != ";
            echo $new_course_data->{$obj_field_name} . "\n";
          }
          $update_fields[$field_name] = $new_course_data->{$obj_field_name};
        }
      }
    }

    unset($update_fields["asc_courses_processed_id"]);  // doesn't come from API
    unset($update_fields["updated"]);  // doesn't come from API
    unset($update_fields["asc_courses_api_data_id"]);  // doesn't come from API
    unset($update_fields["dept_org"]);  // doesn't come from API
    unset($update_fields["strm"]);  // we do not care about duplicates with different 'strm' values

    if(empty($update_fields) && $this->debug) {
      echo "updateProcessedCourse(): There is no information to update\n";
    }
    else {
      // echo "existing_row: " . print_r($existing_row, true) . "\n";
      // echo "course data: " . print_r($new_course_data, true) . "\n";
      $update_fields["updated"] = time();
      $update_fields["asc_courses_api_data_id"] = $new_course_data->{'asc_courses_api_data_id'};
      if ($this->debug) echo "Update fields: " . print_r($update_fields, true) . "\n";

      $update_query_result = $this->db->update("asc_courses_processed")
          ->fields($update_fields)
          ->condition("asc_courses_processed_id", $existing_row["asc_courses_processed_id"], "=")
          ->execute();
      if($update_query_result != 1) {
        echo "updateProcessedCourse(): Unexpected number of rows updated: $update_query_result\n";
      }
    }
    if ($this->debug) echo "\n";
    return $update_query_resuilt;
  }



  /**
   * UNUSED - Fetch and store a list of D-Orgs
   */
  public function fetchDorgList() {
    if($this->debug) echo "============ AscCoursesApi::fetchDorgList() =============\n";
    $access_token = $this->getAccessToken();
    $dorg_data_url = $this->base_url . "dept/1.0/detail";

    if($this->debug) echo "D-org data url: $dorg_data_url\n";
    $dorg_data_headers = [
      "Accept: application/json",
      "Authorization: Bearer $access_token",
      "Content-Type: application/json"
    ];

    $dorg_post_data = "{";
    $dorg_post_data .= '"VP_CATEGORY": "ACADEMIC", ';
    $dorg_post_data .= '"VP_AREA": "ARTS_AND_SCIENCES", ';
    $dorg_post_data .= '"VP_COLLEGE": "", ';
    $dorg_post_data .= '"COLLEGE1": "", ';
    $dorg_post_data .= '"COLLEGE2": "", ';
    $dorg_post_data .= '"DEPARTMENT": "", ';
    $dorg_post_data .= '"ORGANIZATION": "", ';
    $dorg_post_data .= '"INCLUDE_HISTORY": "", ';
    $dorg_post_data .= '"EFFDT_YYYYMMDD": ""';
    $dorg_post_data .= "}";

    if($this->debug) echo "D-org POST data: $dorg_post_data\n";

    /*
      $dorg_post_data = [
        "VP_CATEGORY" => "ACADEMIC",
        "VP_AREA" => "",
        "VP_COLLEGE" => "",
        "COLLEGE1" => "",
        "COLLEGE2" => "",
        "DEPARTMENT" => "",
        "ORGANIZATION" => "",
        "INCLUDE_HISTORY" => "",
        "EFFDT_YYYYMMDD" => ""
      ];
      if($this->debug) echo "D-org POST data: " . print_r($dorg_post_data, true) . "\n";
    */

    if(empty($this->ch)) {
      $this->ch = curl_init();
    }
    curl_setopt($this->ch, CURLOPT_URL, $dorg_data_url);

    curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($this->ch, CURLOPT_POST, true);
    curl_setopt($this->ch, CURLOPT_POSTFIELDS, $dorg_post_data);
    curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($this->ch, CURLOPT_HTTPHEADER, $dorg_data_headers);
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);

    if(isset($this->resolve_host)) {
      if ($this->debug) echo "fetchDorgList() - Resolve host set: " . $this->resolve_host . "\n";
      // error_log("fetchDorgList() - Resolve host set: " . $this->resolve_host);
      curl_setopt($this->ch, CURLOPT_RESOLVE, $this->resolve_host);
      curl_setopt($this->ch, CURLOPT_PORT, constant($this->soip_constant_name));
    }

    if($this->debug > 1) {
      curl_setopt($this->ch, CURLOPT_VERBOSE, true);
    }

    $curl_start = microtime(true);
    $dorg_data_result = curl_exec($this->ch);
    $curl_error = curl_error($this->ch);
    // curl_close($this->ch);
    $curl_finish = microtime(true);
    $curl_seconds = $curl_finish - $curl_start;
    if ($this->debug) echo "fetchDorgList() - CURL error: " . print_r($curl_error, true) . "\n";

    if(!empty($curl_error) || empty($dorg_data_result)) {
      echo "\n$curl_error\n";
      die("AscCoursesApi::fetchDorgList() - Failed to fetch D-org list from the API!!  X_X\n\n");
    }
    else {
      if ($this->debug) echo "D-org data length:" . strlen($dorg_data_result) . "\n";
      if ($this->debug) echo "D-org data in $curl_seconds seconds\n\n";


      /*
        $dorgs = $dorgs->getDeptOrgInfoResponse->OUT_VP_CATEGORY->OUT_VP_AREA;

        // echo "dorgs type: " . gettype($dorgs) . "\n";
        // $dorg_vars = get_object_vars($dorgs);
        // echo "vars: " . print_r(array_keys($dorg_vars), true) . "\n";

        $dorgs = $dorgs->OUT_VP_COLLEGE->OUT_COLLEGE1;
        // print_r($dorgs);

        // echo "dorgs type: " . gettype($dorgs) . "\n";
        // $dorg_vars = get_object_vars($dorgs);
        // print_r($dorg_vars);
        // echo "vars: " . print_r(array_keys($dorgs), true) . "\n";
      */

      /*
        foreach($dorgs as $dorg) {
          echo "==================\n";
          // echo "dorg type: " . gettype($dorg) . "\n";
          // $dorg_vars = get_object_vars($dorg);
          // echo "dorg vars: " . print_r(array_keys($dorg_vars), true) . "\n";

          echo "COLLEGE1: " . $dorg->COLLEGE1 . "\n";
          echo "COLLEGE1_DESCR: " . $dorg->COLLEGE1_DESCR . "\n\n";


          $out_college2 = $dorg->OUT_COLLEGE2;
          // echo "out_college2 type: " . gettype($out_college2) . "\n";
          // echo "out_college2 keys: " . print_r(array_keys($out_college2), true) . "\n";

          foreach ($out_college2 as $college2) {
            echo "------------\n";
            // echo "college2 type: " . gettype($college2) . "\n";
            // $college2_vars = get_object_vars($college2);
            // echo "college2 vars: " . print_r(array_keys($college2_vars), true) . "\n";

            echo "COLLEGE2: " . $college2->COLLEGE2 . "\n";
            echo "COLLEGE2_DESCR: " . $college2->COLLEGE2_DESCR . "\n\n";

            $out_department = $college2->OUT_DEPARTMENT;
            echo "out_department type: " . gettype($out_department) . "\n";
            $out_department_vars = get_object_vars($out_department);
            echo "out_department vars: " . print_r(array_keys($out_department_vars), true) . "\n";
            echo "------------\n";

          }

          // echo "out_vp_college type: " . gettype($dorg->OUT_VP_COLLEGE) . "\n";
          // $out_vp_college_vars = get_object_vars($dorg->OUT_VP_COLLEGE);
          // echo "vars: " . print_r(array_keys($out_vp_college_vars), true) . "\n";
          echo "==================\n\n";
        }
      */

      // $connection = \Drupal::database();
      $row = [
        'date' => time(),
        // 'dept_org' => $dorg,
        'raw_json' => $dorg_data_result
      ];

      // this db table doesn't even exist
      // $this->db->insert('asc_course_dorgs')->fields($row)->execute();

      $dorg_data = json_decode($dorg_data_result);
      return $dorg_data;
    }
  }

  /**
   * UNUSED? - Get a list of distinct D-Org numbers stored in the db
   */
  public function getApiDorgList() {
    if($this->debug) echo "============ AscCoursesApi::getApiDorgList() =============\n";
    $dorg_info_query = $this->db->select('asc_course_dorgs', 'acd')
      // ->fields('acd', array('id', 'date', 'dept_org'))
      ->fields('acd')
      ->orderBy('date', 'DESC')
      ->range(0, 1);
    $dorg_info_result = $dorg_info_query->execute();
    // $rows = $dorg_info_result->fetchAllAssoc('id', \PDO::FETCH_ASSOC);
    if($row = $dorg_info_result->fetchAssoc()) {
      // print_r($row);
      $data_date = $row['date'];
      $data_age = time() - $data_date;
      if($this->debug) echo "Data date: $data_date - " . date("Y-m-d h:i:s", $data_date) . " - $data_age seconds old\n";

      if($data_age < 36000) {
        if($this->debug) echo "Data is $data_age seconds old.. reusing data from database.\n";
        $dorg_json = $row['raw_json'];
        // $len = strlen($dorg_json);
        if($this->debug) echo "JSON Length:" . strlen($dorg_json) . "\n";
        // echo substr($dorg_json, 0, 1024) . "\n\n";
        $json_data = json_decode($dorg_json);
        // print_r($json_data);
        return $json_data;
      }
      else {
        if($this->debug) echo "D-org data is stale.\n";
        return $this->fetchDorgList();
      }
    }
    else {
      if($this->debug) echo "No D-org data in the database.\n";
      return $this->fetchDorgList();
    }
  }

  /**
   * Return hard-coded/statically-defined list of D-Org numbers
   *
   * @return array
   *   The D-Org list
   */
  public static function getDorgList() {
    // if($this->debug) echo "============ AscCoursesApi::getDorgList() =============\n";
    $dorgs = [
      'D0200' => [
        'name' => "Arts Administration",
      ],
      'D0205' => [
        'name' => "Diversity & Identity Studies C",
      ],
      'D0206' => [
        'name' => "Film Studies",
      ],
      'D0208' => [
        'name' => "Arts Initiatives",
      ],
      'D0210' => [
        'name' => "Adv Computing Ctr/Art & Des",
      ],
      'D0215' => [
        'name' => "Art",
      ],
      'D0225' => [
        'name' => "Arts Education",
      ],
      'D0230' => [
        'name' => "Ind, Intr & Visual Comm Design",
      ],
      'D0235' => [
        'name' => "History of Art",
      ],
      'D0241' => [
        'name' => "Dance",
      ],
      'D0262' => [
        'name' => "School of Music",
      ],
      'D0280' => [
        'name' => "Theatre",
      ],
      'D0500' => [
        'name' => "Humanities Administration",
      ],
      'D0502' => [
        'name' => "AfricanAmer&African Studies",
      ],
      'D0505' => [
        'name' => "Ctr Medieval & Ren Studies",
      ],
      'D0506' => [
        'name' => "Women's Studies",
      ],
      // 'D0507' => [
      //   'name' => "Humanities Information Svcs",
      // ],
      'D0508' => [
        'name' => "Melton Ctr for Jew Studies",
      ],
      'D0509' => [
        'name' => "Greek and Latin",
      ],
      'D0518' => [
        'name' => "Dept of Comp Stds in Hum",
      ],
      'D0527' => [
        'name' => "East Asian Languages & Lit",
      ],
      'D0536' => [
        'name' => "Ctr-Study&Teaching of Writing",
      ],
      'D0537' => [
        'name' => "English",
      ],
      'D0543' => [
        'name' => "Foreign Language Center",
      ],
      'D0545' => [
        'name' => "French and Italian",
      ],
      'D0544' => [
        'name' => "Cntr for the Study of Religion",
      ],
      'D0547' => [
        'name' => "Germanic Languages & Lit",
      ],
      'D0554' => [
        'name' => "Near Eastern Lang & Culture",
      ],
      'D0557' => [
        'name' => "History",
      ],
      'D0564' => [
        'name' => "Humanities Institute",
      ],
      'D0566' => [
        'name' => "Linguistics",
      ],
      'D0575' => [
        'name' => "Philosophy",
      ],
      'D0593' => [
        'name' => "Slavic & East European L&L",
      ],
      'D0596' => [
        'name' => "Spanish and Portugese",
      ],
      // 'D0300' => [
      //   'name' => "Biological Sciences Admin",
      // ],
      'D0303' => [
        'name' => "Ancillary Fac & Services",
      ],
      // 'D0321' => [
      //   'name' => "Div of Sensory Biophysics",
      // ],
      'D0326' => [
        'name' => "Introductory Biology",
      ],
      'D0340' => [
        'name' => "Molecular Genetics",
      ],
      'D0350' => [
        'name' => "Microbiology",
      ],
      'D0385' => [
        'name' => "Plant Biotechnology",
      ],
      'D0386' => [
        'name' => "PMGF",
      ],
      'D0390' => [
        'name' => "EEOB",
      ],
      'D0397' => [
        'name' => "Ohio Biological Survey",
      ],
      // 'D0600' => [
      //   'name' => "Math & Physical Sci Admin",
      // ],
      'D0614' => [
        'name' => "Astronomy",
      ],
      'D0628' => [
        'name' => "Chemistry",
      ],
      'D0656' => [
        'name' => "Geological Sciences",
      ],
      'D0671' => [
        'name' => "Mathematics",
      ],
      'D0684' => [
        'name' => "Physics",
      ],
      'D0694' => [
        'name' => "Statistics",
      ],
      // 'D0700' => [
      //   'name' => "Social & Behav Sci Admin",
      // ],
      // 'D0701' => [
      //   'name' => "Survey Research",
      // ],
      'D0703' => [
        'name' => "Ctr/Human Resource Rsch",
      ],
      'D0707' => [
        'name' => "Mershon Center for Education",
      ],
      'D0708' => [
        'name' => "Population Research Center",
      ],
      'D0709' => [
        'name' => "UG Intl Studies Program",
      ],
      'D0711' => [
        'name' => "Anthropology",
      ],
      'D0722' => [
        'name' => "Economics",
      ],
      'D0733' => [
        'name' => "Geography",
      ],
      'D0735' => [
        'name' => "Urban & Regional Analysis Init",
      ],
      'D0744' => [
        'name' => "Journalism & Communication",
      ],
      'D0755' => [
        'name' => "Political Science",
      ],
      'D0766' => [
        'name' => "Psychology",
      ],
      'D0777' => [
        'name' => "Sociology",
      ],
      'D0778' => [
        'name' => "Criminal Justice Research Ctr",
      ],
      'D0780' => [
        'name' => "Cntr Cognitive & Brain Science",
      ],
      'D0781' => [
        'name' => "Cntr Cog & Behavioral Brain Im",
      ],
      // 'D0782' => [
      //   'name' => "Inst for American Democracy",
      // ],
      'D0783' => [
        'name' => "Neuroscience Ungrad Major",
      ],
      'D0784' => [
        'name' => "Behavioral Decision Making",
      ],
      'D0799' => [
        'name' => "Speech and Hearing",
      ],

      // ENGINEERING DORGS
      'D1407' => [
        'name' => "Aeronautical and Astronautical Engineering",
      ],
      'D1425' => [
        'name' => "Chemical and Biomolecular Engineering",
      ],
      'D1435' => [
        'name' => "Computer Science and Engineering",
      ],
      'D1445' => [
        'name' => "Electrical and Computer Engineering",
      ],
      'D1457' => [
        'name' => "Integrated Systems Engineering",
      ],
      'D1468' => [
        'name' => "Materials Science and Engineering",
      ],
      'D1470' => [
        'name' => "Mechanical Engineering",
      ],
      'D1470' => [
        'name' => "Nuclear Engineering",
      ],
    ];

    return $dorgs;
  }
}
