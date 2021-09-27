<?php

namespace Drupal\linkit\Plugin\Linkit\Matcher;

use Drupal\linkit\MatcherBase;
use Drupal\linkit\Suggestion\DescriptionSuggestion;
use Drupal\linkit\Suggestion\SuggestionCollection;

/**
 * Provides a linkit matcher for route:<nolink>.
 *
 * @Matcher(
 *   id = "nolink",
 *   label = @Translation("Nolink"),
 * )
 */
class NolinkMatcher extends MatcherBase {

  /**
   * {@inheritdoc}
   */
  public function execute($string) {
    $suggestions = new SuggestionCollection();

    // Check for the text 'nolink' (e.g. like route:<nolink> with core link
    // fields) and return route:<nolink> if it exists.
    if (strpos($string, 'nolink') !== FALSE) {
      $suggestion = new DescriptionSuggestion();
      $suggestion->setLabel($this->t('Empty link'))
        ->setPath('route:<nolink>')
        ->setGroup($this->t('System'))
        ->setDescription($this->t('An empty link'));

      $suggestions->addSuggestion($suggestion);
    }
    return $suggestions;
  }

}
