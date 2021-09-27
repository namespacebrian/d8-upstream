<?php

namespace Drupal\asc_courses\Commands;

// use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drush\Commands\DrushCommands;
use \Drupal\node\Entity\Node;

use \Drupal\asc_courses\AscCoursesApi;
use \Drupal\asc_courses\AscCoursesImporter;

use \Drupal\Core\Database\Database;


/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class AscCoursesCommands extends DrushCommands {

  // public $soip_constant_name = "PANTHEON_SOIP_EIP";
  // public $soip_constant_name = "PANTHEON_SOIP_EIP_PROD";

  /**
   * Dummy command for running whatever arbitrary code I want
   *
   * @param $arg1
   *   Argument description.
   * @param array $options
   *   An associative array of options whose values come from cli, aliases, config, etc.
   * @option option-name
   *   Description
   * @usage asc_courses-commandName foo
   *   Usage description
   *
   * @command asc_courses:commandName
   * @aliases foo
   */
  public function commandName($arg1, $options = ['option-name' => 'default']) {

    $api = new AscCoursesApi();
    $api->processPendingApiData();
    // print_r($api->fetchDorgCourses('D0506'));

    // $api->getAccessToken();
    // $dorg = $api->config->get('asc_courses.dept_org');
    // echo "D-org: $dorg\n";
    // $courses = $api->fetchDorgCourses($dorg);

    // module_load_install('asc_courses');
    // $schema = Database::getConnection()->schema();
    // $tables = asc_courses_schema();


    // $importer = new AscCoursesImporter();
    // $importer->debug = 2;
    // $api_response = $importer->loadSubjectDataFromDatabase($arg1);
    // $course_data = $api_response->getCourseCatalogResponse->catalog->course;
    // echo "\n";
    // echo count($course_data) . " courses in $arg1\n\n";

    // foreach ($course_data as $course) {
    //   $eip_id = $course->{'crse-id'};
    //   $subj_abbrev = $course->{'subject'};
    //   $cat_num = $course->{'catalog-nbr'};
    //   $title = $course->{'course-title-long'};
    //   echo "$eip_id - $subj_abbrev - $cat_num - $title\n";
    // }
    // $importer->fetchAndImportAll();

    // $importer->establishApiReferences();
  }


  /**
   * Retrieve an EIP access token
   *
   * @param array $options
   *   An associative array of options whose values come from cli, aliases, config, etc.
   * @option option-name
   *   Description
   * @usage asc-courses-access-token
   *   Usage description
   *
   * @command asc_courses:access-token
   * @aliases asc-courses-access-token
   */
  public function accessToken($options = ['option-name' => 'default']) {
    $api = new AscCoursesApi();
    // $api->debug = 1;
    $result = $api->fetchAccessToken();

    if(isset($result->error)) {
      echo "\n";
      echo "Error fetching access token: " . $result->error . " - " . $result->error_description . "\n";
      return 1;
    }
    else if (isset($result->access_token)) {
      echo "Access token: " . $result->access_token . "\n";
      echo "Expires in " . $result->expires_in . " seconds.\n";
      return 0;
    }
    else {
      echo "Unrecogized response from API: " . print_r($result, true) . "\n";
      return 1;
    }
  }

  /**
   * Pull fresh data for all D-Orgs from the API to database
   *
   * @param array $options
   *   An associative array of options whose values come from cli, aliases, config, etc.
   * @option option-name
   *   Description
   * @usage asc_courses-pull-all-dorgs
   *   Usage description
   *
   * @command asc_courses:pull-all-dorgs
   * @aliases asc-pull-all-dorgs
   */
  public function pullAllDorgs($options = ['option-name' => 'default']) {
    $api = new AscCoursesApi();
    $api->debug = 0;

    $dorgs = $api->getDorgList();
    $dorg_numbers = array_keys($dorgs);

    echo "Dorg count: " . count($dorg_numbers) . "\n";

    foreach($dorg_numbers as $dorg_number) {
      echo "=== $dorg_number - " . $dorgs[$dorg_number]["name"];
      $courses = $api->fetchDorgCourses($dorg_number, true);
      echo "\n";
    }

  }


  /**
   * Process all unprocessed API data
   *
   * @param array $options
   *   An associative array of options whose values come from cli, aliases, config, etc.
   * @option option-name
   *   Description
   * @usage asc_courses-commandName [count]
   *   Usage description
   *
   * @command asc_courses:process-api-data
   * @aliases asc-courses-process-api-data
   */
  public function processApiData($count = null, $options = ['option-name' => 'default']) {
    $api = new AscCoursesApi();
    //$api->debug = 1; // apparently we're hard-coding debug mode now

    if(is_numeric($count)) {
      echo "count argument specified: $count\n";
      $api->processPendingApiData(null, $count);
    }
    else {
      // echo "Count is NOT numeric: $count\n";
      $api->processPendingApiData();
    }

  }


  /**
   * Command description here.
   *
   * @param array $options
   *   An associative array of options whose values come from cli, aliases, config, etc.
   * @option option-name
   *   Description
   * @usage asc_courses-create-courses
   *   Usage description
   *
   * @command asc_courses:create-courses
   * @aliases asc-create-courses
   */
  public function createCourses($options = ['option-name' => 'default']) {
    $importer = new AscCoursesImporter();
    $importer->debug = 2;
    $importer->fetchAndImportAll();
  }


  /**
   * Establish EIP references for course nodes lacking them -- i.e. Course nodes which
   * were not created by this module, such as migrated Course nodes
   *
   * @param array $options
   *   An associative array of options whose values come from cli, aliases, config, etc.
   * @option option-name
   *   Description
   * @usage asc_courses-link-nodes-to-api
   *   Usage description
   *
   * @command asc_courses:link-nodes-to-api
   * @aliases asc-link-nodes-to-api
   */
  public function linkNodesToApi($options = ['option-name' => 'default']) {
    $importer = new AscCoursesImporter();
    $importer->debug = 2;
    // $importer->fetchAndImportAll();
    // print_r($importer->loadSubjectDataFromDatabase('D0506'));
    $importer->establishApiReferences();
  }


  /**
   * An example of the table output format.
   *
   * @param $nid
   *   ID of the node to be printed.
   * @param array $options An associative array of options whose values come from cli, aliases, config, etc.
   *
   * @field-labels
   *   group: Group
   *   token: Token
   *   name: Name
   * @default-fields group,token,name
   *
   * @command asc_courses:print-node
   * @aliases asc-print-node
   *
   * @filter-default-field name
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   */
  public function printNode($nid, $options = ['option-name' => 'default']) {
    $asdf_node = Node::load($nid);
    // print_r($asdf_node);

    $asdf_body = $asdf_node->get('body')->getValue();
    echo "body: " . print_r($asdf_body, true) . "\n";

    $asdf_desc = $asdf_node->get('field_course_description')->getValue();
    echo "description: " . print_r($asdf_desc, true) . "\n";

    $asdf_course_number = $asdf_node->get('field_course_number')->getValue();
    echo "course_number: " . print_r($asdf_course_number, true) . "\n";

    $asdf_credit_hours = $asdf_node->get('field_credit_hours')->getValue();
    echo "credit_hours: " . print_r($asdf_credit_hours, true) . "\n";

    // $asdf_eip_id = $asdf_node->get('field_eip_id')->getValue();
    // echo "eip_id: " . print_r($asdf_eip_id, true) . "\n";

    // $asdf_offered_autumn = $asdf_node->get('field_offered_autumn')->getValue();
    // echo "offered_autumn: " . print_r($asdf_offered_autumn, true) . "\n";

    // $asdf_offered_spring = $asdf_node->get('field_offered_spring')->getValue();
    // echo "offered_spring: " . print_r($asdf_offered_spring, true) . "\n";

    // $asdf_offered_summer = $asdf_node->get('field_offered_summer')->getValue();
    // echo "offered_summer: " . print_r($asdf_offered_summer, true) . "\n";

    // $asdf_subject_abbreviation = $asdf_node->get('field_subject_abbreviation'->getValue();
    // echo "subject_abbreviation: " . print_r($asdf_subject_abbreviation, true) . "\n";

    // $node_entity_type = \Drupal::entityTypeManager()->getDefinition('node');
    // $bundle_key = $node_entity_type = \Drupal::entityTypeManager()->getDefinition('node');
    // echo "bundle key: " . $bundle_key->getKey() . "\n";

  }


}
