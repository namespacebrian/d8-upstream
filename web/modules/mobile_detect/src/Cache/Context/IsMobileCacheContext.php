<?php

namespace Drupal\mobile_detect\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Detection\MobileDetect;

/**
 * Defines the 'Is mobile' cache context.
 *
 * Cache context ID: 'is_mobile'.
 */
class IsMobileCacheContext implements CacheContextInterface {

  /**
   * @var \Detection\MobileDetect
   */
  protected $mobileDetect;

  /**
   * Constructs an IsFrontPathCacheContext object.
   *
   * @param \Detection\MobileDetect $path_matcher
   */
  public function __construct(MobileDetect $mobile_detect) {
    $this->mobileDetect = $mobile_detect;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Is mobile');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    return (string) $this->mobileDetect->isMobile();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
