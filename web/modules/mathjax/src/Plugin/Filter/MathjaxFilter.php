<?php

namespace Drupal\mathjax\Plugin\Filter;

use Drupal\filter\Plugin\FilterBase;
use Drupal\filter\FilterProcessResult;
use Drupal\Core\Url;

/**
 * Provides a filter to format text with MathJax.
 *
 * Wraps the text in a div with a class name that is looked-for
 * by the MathJax Javascript library.
 *
 * @Filter(
 *   id = "filter_mathjax",
 *   module = "mathjax",
 *   title = @Translation("MathJax"),
 *   description = @Translation("Mathematics inside the configured delimiters is rendered by MathJax."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 *   weight = 50
 * )
 */
class MathjaxFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $wrapped = strip_tags($text) !== 'TEST' ? '<div class="tex2jax_process">' . $text . '</div>' : $text;
    $result = new FilterProcessResult($wrapped);
    $config = \Drupal::config('mathjax.settings');
    $config_type = $config->get('config_type');
    if ($config_type == 0) {
      $result->setAttachments([
        'library' => [
          'mathjax/config',
          'mathjax/source',
          'mathjax/setup',
        ],
        'drupalSettings' => [
          'mathjax' => [
            'config_type' => $config_type,
            'config' => json_decode($config->get('default_config_string')),
          ],
        ],
      ]);
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('<span class="tex2jax_ignore">Mathematics inside the <a href=":url">configured delimiters</a> is
      rendered by MathJax. The default math delimiters are $$...$$ and \[...\] for
      displayed mathematics, and $...$ and \(...\) for in-line mathematics.</span>',
        [':url' => Url::fromRoute('mathjax.settings')->toString()]
    );
  }

}
