<?php
namespace Drupal\asc_courses;

// use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
// use Drush\Commands\DrushCommands;
use \Drupal\Core\Database\Database;
use \Drupal\Core\Config;
use \Drupal\node\Entity\Node;

/**
 * Generate and update courses Nodes
 */
class AscCoursesImporter {
  public $debug = 0;
  public $config;
  public $api;
  public $db;

  public $created_nodes = [];
  public $updated_nodes = [];

  /**
   * {@inheritdoc}
   */
  public function __construct($config = null, $db = null) {
    // Load configuration if not passed
    if (!empty($config)) {
      $this->config = $config;
    }
    else {
      $this->config = \Drupal::service('config.factory')->get('asc_courses.settings');
    }

    // Get database connection
    if(empty($db)) {
      $this->db = \Drupal::database();
    }
    else {
      $this->db = $db;
    }

  }

  public function importCourseNodes($courses_data) {
    if($this->debug) echo "importCourseNodes()\n";

    foreach($courses_data as $course_data) {
      // echo "importCourseNodes(): " . print_r($course_data, true) . "\n";
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      // if($this->debug) echo "$i catalog-nbr: " . $course_data['catalog-nbr'] . "\n";
      // if($this->debug > 1) echo "importCourseNodes() - " . print_r($course_data, true);

      // look up existing node
      $node_query = \Drupal::entityQuery('node')
        ->condition('field_eip_id', $course_data['crse_id'], '=');
      $existing_node = $node_query->execute();

      if(empty($existing_node)) {
        $this->createCourseNode($node_storage, $course_data);
      }
      else {
        // update existing node
        $existing_nid = array_shift($existing_node);
        $this->updateCourseNode($node_storage, $existing_nid, $course_data);
        // if($this->debug) echo "\n";
      }
    }
  }

  public function createCourseNode($node_storage, $course_data) {
    if($this->debug) echo "createCourseNode() - create new node\n";
    // $course_node = Node::create([
    $course_node = $node_storage->create(array(
      'type'                        => 'course',
      'title'                       => $course_data['course_title_long'],
      'field_course_description'    => [$course_data['descrlong']],
      'field_course_number'         => [$course_data['catalog_nbr']],
      'field_credit_hours'          => [$course_data['acad_prog']],
      'field_subject_abbreviation'  => [$course_data['subject']],
      'field_eip_id'                => [$course_data['crse_id']],
    ));
    $course_node->save();

    $new_course_nid = $course_node->id();
    $this->created_nodes[$new_course_nid] = 1;  // track this change

    if($this->debug) echo "createCourseNode() - New course nid[$new_course_nid] catalog[" . $course_data['catalog-nbr'] . "]\n";
  }

  public function updateCourseNode($node_storage, $nid, $course_data) {
    if($this->debug) echo "updateCourseNode() - Existing nid: $nid\n";

    $course_node = $node_storage->load($nid);

    // Compare values and update if necessary
    $content_changed = false;

    // $title = $course_node->get('title')->getValue()[0]['value'];
    // echo "title: " . print_r($title, true);
    $title = $course_node->title->value;
    if ($title != $course_data['course_title_long']) {
      if($this->debug) {
        echo "### Title changed..\n";
        echo "Node title: $title\n";
        echo "API title: " . $course_data['course_title_long'] . "\n";
      }
      $content_changed = true;
      // $course_node->setTitle($course_data['course_title_long']);
      $course_node->title->value = $course_data['course_title_long'];
    }

    $description = $course_node->field_course_description->value;
    // echo "description: $description\n";
    if ($description != $course_data['descrlong']) {
      if($this->debug) {
        echo "### description changed..\n";
        echo "Node description: $description\n";
        echo "API description: " . $course_data['descrlong'] . "\n";
      }
      $content_changed = true;
      $course_node->field_course_description->value = $course_data['descrlong'];
    }

    // $course_number = $course_node->get('field_course_number')->getValue()[0]['value'];
    // echo "course_number: " . print_r($course_number, true);
    $course_number = $course_node->field_course_number->value;
    if ($course_number != $course_data['catalog_nbr']) {
      if($this->debug) {
        echo "### course_number changed..\n";
        echo "Node course_number: $course_number\n";
        echo "API course_number: " . $course_data['catalog_nbr'] . "\n";
      }
      $content_changed = true;
      // $course_node->set('field_course_number', $course_data['catalog_nbr']);
      $course_node->field_course_number->value = $course_data['catalog_nbr'];
    }

    $credit_hours = $course_node->field_credit_hours->value;
    if ($credit_hours != $course_data['acad_prog']) {
      if($this->debug) {
        echo "### credit_hours changed..\n";
        echo "Node credit_hours: $credit_hours\n";
        echo "API credit_hours: " . $course_data['acad_prog'] . "\n";
      }
      $content_changed = true;
      $course_node->field_credit_hours->value = $course_data['acad_prog'];
    }

    $subj_abbrev = $course_node->field_subject_abbreviation->value;
    if ($subj_abbrev != $course_data['subject']) {
      if($this->debug) {
        echo "### subj_abbrev changed..\n";
        echo "Node subj_abbrev: $subj_abbrev\n";
        echo "API subj_abbrev: " . $course_data['subject'] . "\n";
      }
      $content_changed = true;
      $course_node->field_subject_abbreviation->value = $course_data['subject'];
    }

    if($content_changed) {
      if($this->debug) echo "\nContent has changed.. save updated node.\n\n";
      $course_node->save();
      if(!array_key_exists($nid, $this->created_nodes)) {
        $this->updated_nodes[$nid] = 1;   // track this change
      }
    }
  }


  public function fetchAndImportAll() {
    // $this->config = \Drupal::service('config.factory')->get('asc_courses.settings');
    if(!isset($this->api)) $this->api = new AscCoursesApi($this->config);

    $config_dorgs = $this->config->get('asc_courses.dept_org');

    $dorgs = explode(',', trim($config_dorgs));

    foreach($dorgs as $dorg) {
      $dorg = trim($dorg);
      if ($this->debug) echo "D-org: $dorg\n";

      $courses_data = $this->loadSubjectDataFromDatabase($dorg);

      $this->importCourseNodes($courses_data);
    }

    $config_alacarte = $this->config->get('asc_courses.individual_courses');
    if ($this->debug) echo "config_alacarte: $config_alacarte\n";

    $alacarte = explode(',', trim($config_alacarte));

    $course_info_query = $this->db->select('asc_courses_processed', 'acp')
      ->fields('acp')
      ->condition('crse_id', $alacarte, 'IN');
    $course_info_result = $course_info_query->execute();

    $alacarte_rows = [];
    while ($row = $course_info_result->fetchAssoc()) {
      if ($this->debug > 1) print_r($row);
      $alacarte_rows[] = $row;
    }

    $this->importCourseNodes($alacarte_rows);

  }

  public function loadSubjectDataFromDatabase($dorg) {
    if ($this->debug) echo "loadSubjectDataFromDatabase(): dorg[$dorg]\n";

    $course_info_query = $this->db->select('asc_courses_processed', 'acp')
      ->fields('acp')
      ->condition('dept_org', $dorg, '=');
    $course_info_result = $course_info_query->execute();

    // $rows = $course_info_result->fetchAllAssoc('id', \PDO::FETCH_ASSOC);
    $rows = [];
    while ($row = $course_info_result->fetchAssoc()) {
      // print_r($row);
      $rows[] = $row;
    }
    // print_r($rows);
    // die();
    return $rows;
  }

  public function getDorgToSubject() {
    $dorg_to_name = [
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
    ];

    return $dorg_to_name;
  }


  public function getAllCoursesBySubject() {
    $dorg_to_subject = $this->getDorgToSubject();
    // echo "dorg-to-subject: " . print_r($dorg_to_subject) . "\n";

    $course_info_query = $this->db->select('asc_courses_processed', 'acp')
      // ->fields('acd', array('id', 'date', 'dept_org'))
      ->fields('acp')
      ->orderBy('updated', 'DESC');
      // ->range(0, 1);
    $course_info_result = $course_info_query->execute();

    $all_courses_by_subject = [];

    while($row = $course_info_result->fetchAssoc()) {
      // if ($this->debug) echo "==============\n";
      // echo "Row: " . print_r($row, true) . "\n";

      $dorg_num = $row['dept_org'];
      $dorg_subject = $dorg_to_subject[$dorg_num]['name'];

      $course_subj = $row['subject'];
      $course_cat_nbr = $row['catalog_nbr'];
      // $course_title = $row['course-title-long'];

      if(!array_key_exists($course_subj, $all_courses_by_subject)) {
        $all_courses_by_subject[$course_subj] = [];
      }

      if(!array_key_exists($course_cat_nbr, $all_courses_by_subject[$course_subj])) {
        $all_courses_by_subject[$course_subj][$course_cat_nbr] = [];
      }

      $all_courses_by_subject[$course_subj][$course_cat_nbr][] = $row;

      /*
        $json_length = strlen($row['raw_json']);
        $json_data = json_decode($row['raw_json'])->getCourseCatalogResponse;
        $result = $json_data->result;
        $message = $json_data->message;

        // if ($this->debug) echo  "$dorg_num - $json_length bytes - $dorg_subject - ";

        if(is_array($json_data->catalog->course)) {
          // if ($this->debug) echo count($json_data->catalog->course) . " courses\n";

          foreach($json_data->catalog->course as $course_data) {
            $course_subj = (string)$course_data->{'subject'};
            $course_cat_nbr = (string)$course_data->{'catalog-nbr'};
            $course_title = (string)$course_data->{'course-title-long'};


            // if ($this->debug) echo "$course_subj - $course_cat_nbr - $course_title\n";

            if(!array_key_exists($course_subj, $all_courses_by_subject)) {
              $all_courses_by_subject[$course_subj] = [];
            }

            if(!array_key_exists($course_cat_nbr, $all_courses_by_subject[$course_subj])) {
              $all_courses_by_subject[$course_subj][$course_cat_nbr] = [];
            }

            $all_courses_by_subject[$course_subj][$course_cat_nbr][] = $course_data;
          }
        }
        else {
          // echo "no courses\n";
        }
      */


      // if ($this->debug) echo "===\n";
    }

    // echo "\n\nAll courses: \n";
    // print_r($all_courses_by_subject);

    // echo "\nDUN\n";
    return $all_courses_by_subject;
  }

  public function establishApiReferences() {
    // if(empty($this->config)) {
    //   $this->config = \Drupal::service('config.factory')->get('asc_courses.settings');
    // }
    // $config_dorgs = $this->config->get('asc_courses.dept_org');
    // $dorgs = explode(',', trim($config_dorgs));

    $all_courses = $this->getAllCoursesBySubject();
    // print_r($all_courses);
    // print_r($all_courses['GEOG']);
    // print_r($all_courses['ATMOSSC']);
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

    $node_query = \Drupal::entityQuery('node')
    ->condition('field_eip_id', NULL, 'IS NULL')
    ->condition('type', 'course', '=');
    $node_query_result = $node_query->execute();
    // print_r($orphaned_nodes);

    $orphaned_nodes = [];
    foreach ($node_query_result as $result_nid) {
      $orphaned_nodes[] = $result_nid;
    }
    if ($this->debug) echo "\n" . count($orphaned_nodes) . " course nodes lack references.\n\n";

    $lookup_failed = [];

    foreach ($orphaned_nodes as $node_id) {
      $node = $node_storage->load($node_id);
      $cat_num = $node->field_course_number->value;
      $subj_abbrev = $node->field_subject_abbreviation->value;
      $title = $node->title->value;
      if ($this->debug) echo "$node_id - $cat_num - $subj_abbrev - $title\n";

      if(!array_key_exists($subj_abbrev, $all_courses)) {
        if ($this->debug) echo "No courses with subject $subj_abbrev!!!\n";
        if(!array_key_exists($subj_abbrev, $lookup_failed)) {
          $lookup_failed[$subj_abbrev] = "Not in data set at all!";
        }
      }
      else if (!array_key_exists($cat_num, $all_courses[$subj_abbrev])) {
        if ($this->debug) echo "$subj_abbrev has no courses with catalog number $cat_num!!\n";
        if(!array_key_exists($subj_abbrev, $lookup_failed)) {
          $lookup_failed[$subj_abbrev] = [];
        }
        $lookup_failed[$subj_abbrev][$node_id] = $cat_num;
      }
      else {
        $crse_id = false;
        foreach($all_courses[$subj_abbrev][$cat_num] as $found_course) {
          if ($this->debug) echo "ID: " . $found_course['crse_id'] . "\n";
          $crse_id = $found_course['crse_id'];
        }
        if($crse_id) {
          $node->field_eip_id->value = $crse_id;
          $node->save();
        }
      }
      // print_r($all_courses[$subj_abbrev][$cat_num]);
      // echo "===========\n\n";
    }

    if ($this->debug) echo "===============\n";
    if ($this->debug) print_r($lookup_failed);
  }

}
