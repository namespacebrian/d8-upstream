<?php

namespace Drupal\mathjax\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Presents the module settings form.
 */
class MathjaxSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mathjax_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mathjax.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('mathjax.settings');

    $form['test'] = [
      '#type' => 'fieldset',
      '#title' => 'MathJax Test',
    ];
    $form['test']['library'] = [
      '#type' => 'item',
      '#markup' => '<div class="tex2jax_process"><p>If the MathJax library is installed properly, you should see the square root of x here: $ \sqrt{x} $ and the square root of y here: \(\sqrt{y}\)</p><p>$$\text{The quadratic formula should appear here: } x = \frac {-b \pm \sqrt {b^2 - 4ac}}{2a}$$</p><p>\[\text{The cubic equation should appear here: } a x^3\; +\; b x^2\; +\; c x\; +\; d\; =\; 0\]</p></div>',
    ];

    $form['use_cdn'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use MathJax Content Delivery Network (CDN)'),
      '#default_value' => $config->get('use_cdn'),
      '#description' => t('Check this box to load MathJax source from MathJax servers (recommended) or from the link you can provide below. If you do not check this box, see the README about configuring a local MathJax source. <em>MathJax CDN services are provided subject to its <a href=":url">Terms of Service</a> (TOS). By accessing and using the MathJax CDN, you accept and agree to be bound by the terms and provisions of the TOS</em>.', [':url' => 'https://www.mathjax.org/mathjax-cdn-terms-of-service/']),
    ];
    $form['cdn_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('MathJax CDN URL'),
      '#default_value' => $config->get('cdn_url'),
      '#description' => $this->t("Enter the MathJax CDN url here or leave it unchanged to use the one provided by <a target='_blank' href=':url'>www.mathjax.org</a>.", [':url' => 'http://www.mathjax.org']),
      '#states' => [
        'invisible' => [
          ':input[name="use_cdn"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['config_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Configuration Type'),
      '#options' => [
        0 => $this->t('Text Format (Recommended&mdash;Add the MathJax filter to a <a href=":textformats">text format</a>.)', [':textformats' => Url::fromRoute('filter.admin_overview')->toString()]),
        1 => $this->t('Custom'),
      ],
      '#default_value' => $config->get('config_type'),
    ];
    $form['mathjax_note_default'] = [
      '#type' => 'item',
      '#prefix' => '<span class="tex2jax_ignore">',
      '#markup' => $this->t('MathJax
      will be available as a text filter. Mathematics inside the
      default delimiters will be rendered by MathJax. The
      default math delimiters are $$...$$ and \[...\] for displayed mathematics,
      and $...$ and \(...\) for in-line mathematics. <strong>You must add
      the MathJax filter to a <a href=":textformats">text format</a> and put
      MathJax at the bottom of the filter processing order.</strong>', [':textformats' => Url::fromRoute('filter.admin_overview')->toString()]),
      '#suffix' => '</span>',
      '#states' => [
        'invisible' => [
          ':input[name="config_type"]' => ['value' => 1],
        ],
      ],
    ];
    $form['config_string'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Custom configuration'),
      '#default_value' => ($config->get('config_string')) ? $config->get('config_string') : $config->get('default_config_string'),
      '#description' => $this->t("Enter a JSON configuration string as documented on  <a target='_blank' href=':mathjax-help'>MathJax help</a>. Use with caution as you may introduce JavaScript errors.", [':mathjax-help' => 'http://docs.mathjax.org/en/latest/']),
      '#states' => [
        'invisible' => [
          ':input[name="config_type"]' => ['value' => 0],
        ],
      ],
    ];
    $config_type = $config->get('config_type');
    if ($config_type == 0) {
      $config_string = $config->get('default_config_string');
    }
    else {
      $config_string = $config->get('config_string');
    }
    $form['#attached'] = [
      'library' => [
        'mathjax/source',
      ],
      'drupalSettings' => [
        'mathjax' => [
          'config_type' => $config_type,
          'config' => json_decode($config_string),
        ],
      ],
    ];

    $form['enable_for_admin'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable on admin pages.'),
      '#description' => $this->t('Embeds the MathJax code on admin pages.'),
      '#default_value' => $config->get('enable_for_admin'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('mathjax.settings')
      ->set('use_cdn', $form_state->getValue('use_cdn'))
      ->set('cdn_url', $form_state->getValue('cdn_url'))
      ->set('config_type', $form_state->getValue('config_type'))
      ->set('config_string', $form_state->getValue('config_string'))
        ->set('enable_for_admin', $form_state->getValue('enable_for_admin'))
      ->save();
    drupal_flush_all_caches();
    parent::submitForm($form, $form_state);
  }

}
