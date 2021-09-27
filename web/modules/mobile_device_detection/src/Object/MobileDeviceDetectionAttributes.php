<?php

namespace Drupal\mobile_device_detection\Object;

use Symfony\Component\Yaml\Yaml;

/**
 * MobileDeviceDetectionAttributes class.
 */
class MobileDeviceDetectionAttributes {

  /**
   * {@inheritdoc}
   */
  public function get($attribute = NULL) {
    $file = __DIR__ . '/../../config/attributes/attributes.yml';

    if (!file_exists($file)) {
      return FALSE;
    }

    $attributes = Yaml::parse(file_get_contents($file))['attributes'];

    if (!is_null($attribute)) {
      return $attributes[$attribute];
    }
    return $attributes;
  }

}
