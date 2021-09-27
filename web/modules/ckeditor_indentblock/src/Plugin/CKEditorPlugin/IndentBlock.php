<?php

namespace Drupal\ckeditor_indentblock\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginConfigurableInterface;
use Drupal\ckeditor\CKEditorPluginContextualInterface;
use Drupal\ckeditor\CKEditorPluginCssInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "Indent Block" plugin.
 *
 * NOTE: The plugin ID ('id' key) corresponds to the CKEditor plugin name.
 * It is the first argument of the CKEDITOR.plugins.add() function in the
 * plugin.js file.
 *
 * @CKEditorPlugin(
 *   id = "indentblock",
 *   label = @Translation("Indent Block")
 * )
 */
class IndentBlock extends CKEditorPluginBase implements CKEditorPluginContextualInterface, CKEditorPluginConfigurableInterface, CKEditorPluginCssInterface {

  /**
   * {@inheritdoc}
   */
  public function getCssFiles(Editor $editor) {
    return [
      drupal_get_path('module', 'ckeditor_indentblock') . '/css/plugins/indentblock/ckeditor.indentblock.css',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * NOTE: The keys of the returned array corresponds to the CKEditor button
   * names. They are the first argument of the editor.ui.addButton() or
   * editor.ui.addRichCombo() functions in the plugin.js file.
   */
  public function getButtons() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return $this->getLibraryURL();
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled(Editor $editor) {
    // Enable this plugin, if it is configured as being enabled and at least one
    // of the buttons, Indent or Outdent, is enabled.
    $settings = $editor->getSettings();
    if (isset($settings['plugins']['indentblock']) && $settings['plugins']['indentblock']['enable']) {
      foreach ($settings['toolbar']['rows'] as $row) {
        foreach ($row as $group) {
          foreach ($group['items'] as $button) {
            if ($button === 'Indent' || $button === 'Outdent') {
              return TRUE;
            }
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isInternal() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    // The Indent plugin is internal for Drupal 8 CKEditor and thus can't be
    // defined as a dependency.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'indentClasses' => ['Indent1', 'Indent2', 'Indent3', 'Indent4', 'Indent5', 'Indent6', 'Indent7', 'Indent8', 'Indent9', 'Indent10'],
      'indentOffset' => 2,
      'indentUnit' => 'em',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, Editor $editor) {
    $settings = $editor->getSettings();

    $form['enable'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable indentation on paragraphs'),
      '#default_value' => !empty($settings['plugins']['indentblock']) && $settings['plugins']['indentblock']['enable'] === 1 ? $settings['plugins']['indentblock']['enable'] : 0,
    );

    return $form;
  }

  /**
   * Get the CKEditor Indentblock library URL.
   */
  protected function getLibraryURL() {
    // Search for the path under the current site for multisites.
    $directories[] = \Drupal::service('site.path') . "/libraries/";

    // Search also the root 'libraries' directory.
    $directories[] = 'libraries/';

    // Search also at the path for ckeditor plugins.
    $directories[] = 'libraries/ckeditor/plugins/';

    // Installation profiles can place libraries into a 'libraries' directory,
    // so search that too.
    if ($installProfile = \Drupal::installProfile()) {
      $profile_path = drupal_get_path('profile', $installProfile);
      $directories[] = "$profile_path/libraries/";
    }

    foreach ($directories as $dir) {
      if (file_exists(DRUPAL_ROOT . '/' . $dir . 'indentblock/plugin.js')) {
        return $dir . 'indentblock/plugin.js';
      }
    }

    return 'libraries/indentblock/plugin.js';
  }

}
