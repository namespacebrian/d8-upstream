<?php

namespace Drupal\mobile_device_detection\Plugin\views\display_extender;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display_extender\DisplayExtenderPluginBase;

/**
 * Default display extender plugin. It is extends Views display.
 *
 * @ingroup views_display_extender_plugins
 *
 * @ViewsDisplayExtender(
 *   id = "mobile_device_detection",
 *   title = @Translation("Mobile device detection display extender"),
 *   help = @Translation("Mobile device detection settings for this view."),
 *   no_ui = TRUE
 * )
 */
class MobileDeviceDetectionExtenderPlugin extends DisplayExtenderPluginBase {

  /**
   * Provide the key options for this plugin.
   */
  public function defineOptionsAlter(&$options) {
    $options['device_detection'] = [
      'contains' => [
        'title' => ['default' => ''],
        'description' => ['default' => ''],
      ],
    ];
  }

  /**
   * Provide the default summary for options and category in the views UI.
   */
  public function optionsSummary(&$categories, &$options) {
    $categories['device_detection'] = [
      'title' => $this->t('Show "View" on special devices'),
      'column' => 'second',
    ];

    $options['device_detection'] = [
      'category' => 'other',
      'title' => $this->t('Show "View" on special devices'),
      'value' => $this->getDevices() ? implode(', ', $this->getDevices()) : $this->t('none'),
    ];
  }

  /**
   * Provide a form to edit options for this plugin.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    if ($form_state->get('section') == 'device_detection') {
      $form['#title'] .= $this->t('Show "View" on special devices');
      $form['device_detection']['#type'] = 'container';
      $form['device_detection']['#tree'] = TRUE;
      $form['device_detection']['devices'] = [
        '#type' => 'checkboxes',
        '#options' => [
          'mobile' => $this->t('Mobile'),
          'tablet' => $this->t('Tablet'),
          'desktop' => $this->t('Desktop'),
        ],
        '#default_value' => $this->getDevices() ? $this->getDevices() : [],
        '#title' => $this->t('Select device'),
      ];
    }
  }

  /**
   * Validate the options form.
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {}

  /**
   * Handle any special handling on the validate form.
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    if ($form_state->get('section') == 'device_detection') {
      $device_detection = $form_state->getValue('device_detection');
      $this->options['device_detection'] = $device_detection;
    }
  }

  /**
   * Set up any variables on the view prior to execution.
   */
  public function preExecute() {}

  /**
   * Inject anything into the query that the display_extender handler needs.
   */
  public function query() {}

  /**
   * Static member function to list which sections are defaultable.
   */
  public function defaultableSections(&$sections, $section = NULL) {}

  /**
   * Get the selected devices for this display.
   */
  public function getDevices() {
    $devices = isset($this->options['device_detection']) ? $this->options['device_detection'] : NULL;

    if ($devices && isset($devices['devices'])) {
      $devices = array_filter($devices['devices'], function ($var) {
        return($var != FALSE);
      });
    }
    return $devices;
  }

}
