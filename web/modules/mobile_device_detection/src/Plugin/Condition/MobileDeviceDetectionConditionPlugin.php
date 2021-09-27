<?php

namespace Drupal\mobile_device_detection\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * This main class which add ability to determine device.
 *
 * @Condition(
 *   id = "mobile_device_detection_condition_plugin",
 *   label = @Translation("Show it on special devices"),
 * )
 */
class MobileDeviceDetectionConditionPlugin extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['negate'] = [];
    $form['devices'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('When the device is determined'),
      '#default_value' => $this->configuration['devices'],
      '#options' => [
        'mobile' => $this->t('Mobile'),
        'tablet' => $this->t('Tablet'),
        'desktop' => $this->t('Desktop'),
      ],
      '#description' => $this->t('If you select no devices, the condition will evaluate to TRUE for all devices.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'devices' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['devices'] = array_filter($form_state->getValue('devices'));
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $devices = $this->configuration['devices'];

    if (count($devices) > 1) {
      $devices = implode(', ', $devices);
    }
    else {
      $devices = reset($devices);
    }

    if (!empty($this->configuration['negate'])) {
      return $this->t('The device is not @devices', ['@devices' => $devices]);
    }
    else {
      return $this->t('The device is @devices', ['@devices' => $devices]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (empty($this->configuration['devices']) && !$this->isNegated()) {
      return TRUE;
    }

    \Drupal::service('page_cache_kill_switch')->trigger();
    $entity = \Drupal::service('mobile_device_detection.object');

    foreach ($this->configuration['devices'] as $key => $value) {
      if ($key != 'desktop') {
        $func = 'is' . ucfirst($value);

        if (is_callable([$entity, $func]) && $entity->$func()) {
          return TRUE;
        }
      }
      else {
        if (!$entity->isMobile() && !$entity->isTablet()) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

}
