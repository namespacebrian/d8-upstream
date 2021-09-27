<?php

namespace Drupal\asc_courses\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

use \Drupal\asc_courses\AscCoursesApi;
use \Drupal\asc_courses\AscCoursesImporter;

class ApiSettingsForm extends ConfigFormBase {

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

    // QA Consumer key field
    $form['qa_consumer_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('QA Consumer Key'),
      '#default_value' => $config->get('asc_courses.qa_consumer_key'),
      '#description' => $this->t('Consumer key from QA EIP API dashboard'),
    );

    // QA Consumer secret field
    $form['qa_consumer_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('QA Consumer Secret'),
      '#default_value' => $config->get('asc_courses.qa_consumer_secret'),
      '#description' => $this->t('Consumer secret from QA EIP API dashboard.'),
    );

    // Prod Consumer key field
    $form['prod_consumer_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Production Consumer Key'),
      '#default_value' => $config->get('asc_courses.prod_consumer_key'),
      '#description' => $this->t('Consumer key from Production EIP API dashboard'),
    );

    // Prod Consumer secret field
    $form['prod_consumer_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Production Consumer Secret'),
      '#default_value' => $config->get('asc_courses.prod_consumer_secret'),
      '#description' => $this->t('Consumer secret from Production EIP API dashboard.'),
    );

    // EIP environment
    $form['eip_environment'] = array(
      '#type' => 'radios',
      '#title' => $this->t('EIP Environment'),
      '#default_value' => $config->get('asc_courses.eip_environment'),
      '#options' => array(
        'qa' => $this->t('QA'),
        'prod' => $this->t('Production')
      )
    );
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
    $config->set('asc_courses.qa_consumer_key', $form_state->getValue('qa_consumer_key'));
    $config->set('asc_courses.qa_consumer_secret', $form_state->getValue('qa_consumer_secret'));
    $config->set('asc_courses.prod_consumer_key', $form_state->getValue('prod_consumer_key'));
    $config->set('asc_courses.prod_consumer_secret', $form_state->getValue('prod_consumer_secret'));
    $config->set('asc_courses.eip_environment', $form_state->getValue('eip_environment'));
    $config->save();

    return parent::submitForm($form, $form_state);
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

