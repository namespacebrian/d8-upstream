<?php
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Theme\ThemeSettings;
use Drupal\system\Form\ThemeSettingsForm;
use Drupal\Core\Form;

function asc_bootstrap_form_system_theme_settings_alter(&$form, Drupal\Core\Form\FormStateInterface $form_state) {
  $form['asc_bootstrap_settings']['osu_logo'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use OSU Logo in site header'),
    '#default_value' => theme_get_setting('osu_logo', 'asc_bootstrap'),
  );
}
