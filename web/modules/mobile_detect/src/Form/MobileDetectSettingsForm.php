<?php

namespace Drupal\mobile_detect\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Mobile Detect settings for this site.
 */
class MobileDetectSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mobile_detect_mobile_detect_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mobile_detect.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['add_cache_context'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add cache context to every page'),
      '#default_value' => $this->config('mobile_detect.settings')->get('add_cache_context'),
      '#description' => $this->t('If you need <i>is_mobile</i> cache context on every page, check this option.')
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('mobile_detect.settings')
      ->set('add_cache_context', $form_state->getValue('add_cache_context'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
