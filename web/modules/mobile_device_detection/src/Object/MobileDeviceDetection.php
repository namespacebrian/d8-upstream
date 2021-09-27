<?php

namespace Drupal\mobile_device_detection\Object;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * MobileDeviceDetection object.
 */
class MobileDeviceDetection {

  /**
   * A default attributes instance.
   *
   * @var \Drupal\mobile_device_detection\Object\MobileDeviceDetectionAttributes
   */
  private $attributes;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  public $request;

  /**
   * The MobileDeviceDetectionObject mobileHeaders.
   *
   * @var array
   */
  private $mobileHeaders;

  /**
   * The MobileDeviceDetectionObject cloudHeaders.
   *
   * @var array
   */
  private $cloudHeaders;

  /**
   * The MobileDeviceDetectionObject userAgentHeaders.
   *
   * @var array
   */
  private $userAgentHeaders;

  /**
   * The MobileDeviceDetectionObject object.
   *
   * @var object
   */
  private $object;

  /**
   * The constructoror.
   */
  public function __construct($attributes, $request) {
    $this->setAttributes($attributes);
    $this->setRequest($request);
    $this->init();
  }

  /**
   * Initialization.
   */
  private function init() {
    $this->object = new \stdClass();
    $this->object->type = NULL;
    $headers = $this->getRequest()->getCurrentRequest()->server->all();
    $this->setMobileHeaders($headers);
    $this->setCloudHeaders($headers);
    $this->setUserAgentHeaders($this->getAttributes()->get('user_agent_headers'));

    if ($this->check('mobile')) {
      $this->object->type = 'mobile';
    }
    if ($this->check('tablet')) {
      $this->object->type = 'tablet';
    }
  }

  /**
   * Get object.
   */
  public function getObject() {
    if (isset($this->object->type)) {
      $this->getOperatingSystem();
      $this->getBrowser();

      return $this->object;
    }
  }

  /**
   * Is checking mobile or not.
   */
  public function isMobile() {
    return ($this->object->type === 'mobile') ? TRUE : FALSE;
  }

  /**
   * Is checking tablet or not.
   */
  public function isTablet() {
    return ($this->object->type === 'tablet') ? TRUE : FALSE;
  }

  /**
   * Set attributes.
   */
  protected function setAttributes($attributes) {
    $this->attributes = $attributes;
  }

  /**
   * Get attributes.
   */
  protected function getAttributes() {
    return $this->attributes;
  }

  /**
   * Set request.
   */
  protected function setRequest($request) {
    $this->request = $request;
  }

  /**
   * Get request.
   */
  protected function getRequest() {
    return $this->request;
  }

  /**
   * Set headers.
   */
  protected function setMobileHeaders($headers) {
    array_walk($headers, function (&$v, $k) {
      if (substr($k, 0, 5) === 'HTTP_') {
        $this->mobileHeaders[$k] = $v;
      }
    });
  }

  /**
   * Get headers.
   */
  protected function getMobileHeaders() {
    return $this->mobileHeaders;
  }

  /**
   * Set cloud headers.
   */
  protected function setCloudHeaders($headers) {
    array_walk($headers, function (&$v, $k) {
      if (substr(strtolower($k), 0, 16) === 'http_cloudfront_') {
        $this->cloudHeaders[strtoupper($k)] = $v;
      }
    });
  }

  /**
   * Get cloud headers.
   */
  protected function getCloudHeaders() {
    return $this->cloudHeaders;
  }

  /**
   * Set user agent headers.
   */
  protected function setUserAgentHeaders($headers) {
    $this->userAgentHeaders = implode(' ', array_intersect_key($this->getMobileHeaders(), array_flip($headers)));

    if (!$this->userAgentHeaders && !empty($this->getCloudHeaders())) {
      $this->userAgentHeaders = 'Amazon CloudFront';
    }
  }

  /**
   * Get user agent headers.
   */
  protected function getUserAgentHeaders() {
    return $this->userAgentHeaders;
  }

  /**
   * Is checking which kind of device using.
   */
  private function check($type) {
    if ($this->getUserAgentHeaders() === 'Amazon CloudFront') {
      $headers = $this->setCloudHeaders($this->getRequest()->getCurrentRequest()->server->all());

      if (array_key_exists('HTTP_CLOUDFRONT_IS_MOBILE_VIEWER', $headers) && $headers['HTTP_CLOUDFRONT_IS_MOBILE_VIEWER'] === 'true') {
        return TRUE;
      }
    }

    switch ($type) {
      case 'mobile':
        $options = array_merge($this->getAttributes()->get('phone_devices'));
        $headers = array_intersect_key($this->getAttributes()->get('mobile_headers'), $this->getMobileHeaders());

        foreach ($headers as $key => $value) {
          foreach ($value as $v) {
            if (strpos($this->getMobileHeaders()[$key], $v) !== FALSE) {
              return TRUE;
            }
          }
        }

        goto device_detect;
        break;

      case 'tablet':
        $options = array_merge($this->getAttributes()->get('tablet_devices'));
      case 'deviceDetect':
        device_detect:
        $device = false;

        foreach ($options as $value) {
          if (!empty($value)) {
            if ($this->match($value)) {
              $device = TRUE;
            }
          }
        }

        if ($device) {
          foreach ($this->getAttributes()->get('browsers') as $value) {
            if (!empty($value)) {
              if ($this->match($value)) {
                return TRUE;
              }
            }
          }
        }
        break;
    }

    return FALSE;
  }

  /**
   * Get operating system.
   */
  private function getOperatingSystem() {
    $this->object->OS = $this->get($this->getAttributes()->get('operating_systems'));
    $this->version($this->object->OS);
  }

  /**
   * Get browser.
   */
  private function getBrowser() {
    $this->object->browser = $this->get($this->getAttributes()->get('browsers'));
    $this->version($this->object->browser);
  }

  /**
   * Get options.
   */
  private function get($options) {
    foreach ($options as $key => $value) {
      if (!empty($value)) {
        if ($this->match($value)) {
          return $key;
        }
      }
    }
  }

  /**
   * Match headers.
   */
  private function match($value) {
    return (bool) preg_match(sprintf('#%s#is', $value), $this->getUserAgentHeaders(), $matches);
  }

  /**
   * Versions.
   */
  private function version($name) {
    $properties = (array) $this->getAttributes()->get('properties')[$name];

    foreach ($properties as $value) {
      $pattern = str_replace('[VER]', $this->getAttributes()->get('VER'), $value);
      preg_match(sprintf('#%s#is', $pattern), $this->getUserAgentHeaders(), $matches);

      if (!empty($matches)) {
        $this->object->$name[] = $matches;
      }
    }
  }

}
