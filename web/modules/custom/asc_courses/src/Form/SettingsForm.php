<?php

namespace Drupal\asc_courses\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

use \Drupal\asc_courses\AscCoursesApi;
use \Drupal\asc_courses\AscCoursesImporter;

class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'asc_courses_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('asc_courses.settings');

    $selected_courses_delimited = $config->get('asc_courses.individual_courses');
    if(!empty($selected_courses_delimited)) {
      $selected_courses = [];
      // strip extra spaces
      foreach(explode(',', trim($selected_courses_delimited)) as $selected_course) {
        $selected_courses[trim($selected_course)] = 1;
      }
      // dsm($selected_courses);

      // selected courses table header
      $selected_courses_table_header = array(
        'crse_id'           => t('Course ID'),
        'subject'           => t('Subject Abbreviation'),
        'catalog_nbr'       => t('Catalog Number'),
        'course_title_long' => t('Course Title'),
      );

      // get selected courses data from db
      $selected_courses_query = \Drupal::database()->select('asc_courses_processed', 'acp');
      $selected_courses_query->fields('acp', ['crse_id','subject','catalog_nbr','course_title_long']);
      $selected_courses_query->condition('crse_id', array_keys($selected_courses), 'IN');

      $selected_courses_results = $selected_courses_query->execute()->fetchAll();

      // build table rows with search results
      $selected_courses_table_rows=array();

      foreach($selected_courses_results as $data) {
        $selected_courses_table_rows[$data->crse_id] = array(
          'crse_id' => $data->crse_id,
          'subject' => $data->subject,
          'catalog_nbr' => $data->catalog_nbr,
          'course_title_long' => $data->course_title_long,
        );
      }
    }

    // D-Org number
    $form['dept_org'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('D-Org number(s)'),
      '#default_value' => $config->get('asc_courses.dept_org'),
      '#description' => $this->t('D-Org number (i.e. D1435). Multiple numbers may be entered separated by commas'),
    );

    // Individual course IDs
    $form['individual_courses'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Course IDs'),
      '#default_value' => $selected_courses_delimited,
      '#description' => $this->t('Individual course IDs, separated by commas'),
    );

    if(!empty($selected_courses_table_rows)) {
      $form['selected_courses_details'] = [
        '#type' => 'details',
        '#title' => 'Selected Course IDs',
        '#open' => FALSE,
      ];

      // add table to form
      $form['selected_courses_details']['selected_courses_table'] = [
        '#type' => 'table',
        '#header' => $selected_courses_table_header,
        '#rows' => $selected_courses_table_rows,
        // '#weight' => 100,
      ];
    }

    // Import now?
    $form['import_now'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Import now!'),
      // '#default_value' => $config->get('asc_courses.dept_org'),
      '#description' => $this->t('Checking this box will create new and update existing courses.'),
    );


    ////////// Table of D-orgs //////////

    // table header
    $table_header = array(
      'dept_org'        => t('D-ORG'),
      'dorg_title'      => t('D-ORG title'),
      'subject_abbrev'  => t('Subject Abbreviations'),
      // 'subject_name'    => t('Subject Title'),
      // 'catalog_nbr' => t('catalog_nbr'),
      // 'course_title_long' => t('course_title_long'),
    );

    $static_dorglist = AscCoursesApi::getDorgList();

    // select records from db
    $query = \Drupal::database()->select('asc_courses_processed', 'acp');
    $query->fields('acp', ['dept_org']);
    $query->addExpression("group_concat(distinct subject order by subject asc separator ', ')", "subject_abbrevs");
    $query->groupBy('acp.dept_org');
    $query->distinct();

    $dorg_list_results = $query->execute()->fetchAll();

    $dorg_table_rows = [];
    foreach ($dorg_list_results as $dorg_result) {
      $dorg_table_rows[] = [
        "dept_org" => $dorg_result->dept_org,
        "dorg_title"  => $static_dorglist[$dorg_result->dept_org ]['name'],
        "subject_abbrev" => $dorg_result->subject_abbrevs,
      ];
    }

    // $form['dorg_list'] = [
    //   '#type' => 'details',
    //   '#title' => 'D-Org List',
    //   '#open' => TRUE,
    //   '#weight' => 100,
    // ];

    // add table to form
    $form['table'] = [
      '#type' => 'table',
      '#header' => $table_header,
      '#rows' => $dorg_table_rows,
      '#weight' => 100,
      '#empty' => t('No courses found'),
    ];


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('asc_courses.settings');
    $config->set('asc_courses.dept_org', $form_state->getValue('dept_org'));
    $config->set('asc_courses.individual_courses', $form_state->getValue('individual_courses'));
    $config->save();

    $import = $form_state->getValue('import_now');

    // Run courses import if requested
    if($import) {
      \Drupal::logger('asc_courses')->notice("Running courses import.");
      $importer = new AscCoursesImporter();
      $importer->fetchAndImportAll();

      $import_message = '';
      // $import_message = 'Ran courses import. ';

      $import_message .= count($importer->created_nodes) . " courses created. ";
      $import_message .= count($importer->updated_nodes) . " courses updated.";
    }

    // do anyone else's form stuff
    $return = parent::submitForm($form, $form_state);

    // add our own status message last
    if(!empty($import_message)) {
      \Drupal::messenger()->addStatus(t($import_message));
    }

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'asc_courses.settings',
    ];
  }
}

