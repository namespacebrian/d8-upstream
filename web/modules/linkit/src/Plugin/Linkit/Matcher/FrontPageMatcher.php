<?php

namespace Drupal\linkit\Plugin\Linkit\Matcher;

use Drupal\linkit\MatcherBase;
use Drupal\linkit\Suggestion\DescriptionSuggestion;
use Drupal\linkit\Suggestion\SuggestionCollection;
use Drupal\linkit\Utility\LinkitHelper;

/**
 * Provides specific linkit matchers for the front page.
 *
 * @Matcher(
 *   id = "front_page",
 *   label = @Translation("Front page"),
 * )
 */
class FrontPageMatcher extends MatcherBase {

  /**
   * {@inheritdoc}
   */
  public function execute($string) {
    $suggestions = new SuggestionCollection();
    $front_path = '/';
    $query_and_fragment = LinkitHelper::getQueryAndFragment($string);

    if (!empty($query_and_fragment)) {
      $string = substr($string, 0, strpos($string, $query_and_fragment));
    }

    // Special for link to front page.
    if (strpos($string, 'front') !== FALSE || $string == $front_path) {
      $suggestion = new DescriptionSuggestion();
      $suggestion->setLabel($this->t('Front page'))
        ->setPath($front_path . $query_and_fragment)
        ->setGroup($this->t('System'))
        ->setDescription($this->t('The front page for this site.'));

      $suggestions->addSuggestion($suggestion);
    }

    return $suggestions;
  }

}
