<?php

namespace Drupal\social_media\Event;

/**
 * Drupal 9 is shipped with Symfony 4, which places the Event class in a
 * different namespace than Symfony 3 did. In order to support Drupal 8 to
 * Drupal 9 upgrades, we must find whichever class is available and extend it.
 */
if (class_exists('\Symfony\Contracts\EventDispatcher\Event')) {
  class EventProxy extends \Symfony\Contracts\EventDispatcher\Event {
    // Using the Symfony 4 class.
  }
}
elseif (class_exists('\Symfony\Component\EventDispatcher\Event')) {
  class EventProxy extends \Symfony\Component\EventDispatcher\Event {
    // Using the Symfony 3 class.
  }
}
else {
  throw new \Exception('Error resolving Event class.');
}

/**
 * Class SocialMediaEvent.
 */
class SocialMediaEvent extends EventProxy {

  /**
   * TODO describe element.
   *
   * @var array
   */
  protected $element;

  /**
   * Constructor.
   *
   * @param array $element
   *   TODO describe what element is.
   */
  public function __construct(array $element) {
    $this->element = $element;
  }

  /**
   * Return the element.
   *
   * @return array
   *   The element.
   */
  public function getElement() {
    return $this->element;
  }

  /**
   * Element setter.
   *
   * @param array $element
   *   TODO describe what element is.
   */
  public function setElement(array $element) {
    $this->element = $element;
  }

}
