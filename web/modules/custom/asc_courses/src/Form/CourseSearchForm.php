<?php

namespace Drupal\asc_courses\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\TempStore\PrivateTempStore;

// // Other classes I thought I might use...
// use Drupal\Core\Session\AccountInterface;
// use Drupal\Core\Session\SessionManagerInterface;
// use Drupal\user\PrivateTempStoreFactory;
// use \Drupal\asc_courses\AscCoursesApi;
// use \Drupal\asc_courses\AscCoursesImporter;

/**
 * Form for searching and displaying course records from `asc_courses_processed` table
 */
class CourseSearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'asc_courses_search';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Submit buttons
    $form['actions'] = [
      "#type" => "actions",
      'submit' => [
        '#type' => 'submit',
        '#value' => "Search",
        '#name' => "search_button",
        '#button_type' => "primary",
      ],
      'clear' => [
        '#type' => 'submit',
        '#value' => "Clear",
        '#name' => 'clear_button',
      ]
    ];


    // Load saved form values, if any
    $tempstore = \Drupal::service('tempstore.private');
    $store = $tempstore->get('asc_courses_collection');

    $subject = $store->get('search_subject');
    $catalog_nbr = $store->get('search_catalog_nbr');
    $title = $store->get('search_title');


    // main search form
    $form['subject'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Subject Abbreviation'),
      '#default_value' => $subject,
      '#weight' => 1,
    );

    $form['catalog_nbr'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Catalog Number'),
      '#default_value' => $catalog_nbr,
      '#weight' => 20,
    );

    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $title,
      '#weight' => 30,
    );


    // Table of search results, if search terms were provided
    if(!empty($subject) || !empty($catalog_nbr) || !empty($title)) {

      // get individual course selections, for checkboxes
      $config = $this->config('asc_courses.settings');
      $individual_courses = $config->get('asc_courses.individual_courses');

      $selected_courses = [];
      // strip extra spaces
      if(!empty(trim($individual_courses))) {
        foreach(explode(',', trim($individual_courses)) as $selected_course) {
          $selected_courses[trim($selected_course)] = 1;
        }
      }
      // dsm($selected_courses);

      // table header
      $header_table = array(
        'crse_id'           => t('Course ID'),
        'subject'           => t('Subject Abbreviation'),
        'catalog_nbr'       => t('Catalog Number'),
        'course_title_long' => t('Course Title'),
      );

      // select records from db
      $query = \Drupal::database()->select('asc_courses_processed', 'acp');
      $query->fields('acp', ['crse_id','subject','catalog_nbr','course_title_long']);

      if(!empty($subject)) {
        $query->condition('subject', '%'.$subject.'%', 'LIKE');
      }

      if(!empty($catalog_nbr)) {
        $query->condition('catalog_nbr', '%'.$catalog_nbr.'%', 'LIKE');
      }

      if(!empty($title)) {
        $query->condition('course_title_long', '%'.$title.'%', 'LIKE');
      }

      $query->orderBy('acp.subject');
      $query->orderBy('acp.catalog_nbr');

      $results = $query->execute()->fetchAll();

      // build table rows with search results
      $rows=array();

      foreach($results as $data) {

        $rows[$data->crse_id] = array(
          'crse_id' => $data->crse_id,
          'subject' => $data->subject,
          'catalog_nbr' => $data->catalog_nbr,
          'course_title_long' => $data->course_title_long,
        );
      }

      // add table to form
      $form['table'] = [
        '#type' => 'tableselect',
        '#header' => $header_table,
        '#options' => $rows,
        '#weight' => 100,
        '#empty' => t('No courses found'),
        '#default_value' => $selected_courses,
      ];

      $form['save_selections'] = [
        '#type' => 'submit',
        '#value' => "Save selections",
        '#name' => 'save_selections',
        '#weight' => 101,
        '#button_type' => "primary",
      ];
    }
    else { // List of subject abbreviations
      $table_header = array(
        'subject_abbrev'  => t('Subject Abbreviations'),
      );

      $query = \Drupal::database()->select('asc_courses_processed', 'acp');
      $query->fields('acp', ['dept_org','subject']);
      $query->orderBy('acp.subject');
      $query->distinct();

      $subject_results = $query->execute()->fetchAll();
      foreach ($subject_results as $subject_result) {
        $subject_table_rows[] = [
          "subject_abbrev" => $subject_result->subject,
        ];
      }

      // add table to form
      $form['table'] = [
        '#type' => 'table',
        '#header' => $table_header,
        '#rows' => $subject_table_rows,
        '#weight' => 100,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // dsm('Course search form was validated');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Which submit button was clicked?
    $trigger = $form_state->getTriggeringElement();

    if($trigger['#name'] == 'save_selections') {
      $config = \Drupal::service('config.factory')->getEditable('asc_courses.settings');
      $selections_comma_delimited = $config->get('asc_courses.individual_courses');
      // dsm($selections_comma_delimited);

      $selected_courses = [];
      // strip extra spaces
      if(!empty(trim($selections_comma_delimited))) {
        foreach(explode(',', trim($selections_comma_delimited)) as $selected_course) {
          $selected_courses[trim($selected_course)] = 1;
        }
      }
      // dsm($selected_courses);

      $results = $form_state->getValue('table');
      // dsm($results);

      $selections_changed = false;

      foreach($results as $eip_id => $selected) {
        if(array_key_exists($eip_id, $selected_courses) && $selected == 0) {
          // something was removed
          // dsm("$eip_id was removed");

          unset($selected_courses[$eip_id]);
          $selections_changed = true;
        }
        else if ($selected != 0 && !array_key_exists($eip_id, $selected_courses)) {
          // something was added
          // dsm("$eip_id was added");
          $selected_courses[$eip_id] = 1;
          $selections_changed = true;
        }
      }

      if($selections_changed) {
        $selections_comma_delimited = implode(', ', array_keys($selected_courses));
        // dsm("Selections were changed, new value: " . $selections_comma_delimited);

        $config->set('asc_courses.individual_courses', $selections_comma_delimited);
        $config->save();
      }

    }

    else if($trigger['#name'] == 'clear_button') {
      $tempstore = \Drupal::service('tempstore.private');
      $store = $tempstore->get('asc_courses_collection');

      // Clear the search fields
      $store->delete('search_subject');
      $store->delete('search_catalog_nbr');
      $store->delete('search_title');
    }
    else {
      $tempstore = \Drupal::service('tempstore.private');
      $store = $tempstore->get('asc_courses_collection');

      // Save form inputs, for reuse when rebuilding the form
      $store->set('search_subject', $form_state->getValue('subject'));
      $store->set('search_catalog_nbr', $form_state->getValue('catalog_nbr'));
      $store->set('search_title', $form_state->getValue('title'));
    }


    // return parent::submitForm($form, $form_state);
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

