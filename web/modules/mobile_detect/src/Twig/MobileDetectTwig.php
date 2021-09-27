<?php

namespace Drupal\mobile_detect\Twig;

use Twig\TwigFunction;
use Detection\MobileDetect;

/**
 * MobileDetectTwig class.
 *
 */
class MobileDetectTwig extends \Twig_Extension
{

	/**
	 * @var MobileDetect
	 */
	protected $mobileDetector;

	/**
	 * Constructor
	 * @param MobileDetect $mobileDetector
	 */
	public function __construct(MobileDetect $mobileDetector) {
		$this->mobileDetector = $mobileDetector;
	}

	/**
	 * Get extension twig function
	 * @return array
	 */
	public function getFunctions() {
		return array(
			'is_mobile' => new TwigFunction('isMobile', [$this, 'isMobile']),
			'is_tablet' => new TwigFunction('isTablet', [$this, 'isTablet']),
			'is_device' => new TwigFunction('isDevice', [$this, 'isDevice']),
			'is_ios' => new TwigFunction('isIOS', [$this, 'isIOS']),
			'is_android_os' => new TwigFunction('isAndroidOS', [$this, 'isAndroidOS'])
		);
	}

	/**
	 * Is mobile
	 * @return boolean
	 */
	public function isMobile() {
		return $this->mobileDetector->isMobile();
	}

	/**
	 * Is tablet
	 * @return boolean
	 */
	public function isTablet() {
		return $this->mobileDetector->isTablet();
	}

	/**
	 * Is device
	 * @param string $deviceName is[iPhone|BlackBerry|HTC|Nexus|Dell|Motorola|Samsung|Sony|Asus|Palm|Vertu|...]
	 * @return boolean
	 */
	public function isDevice($deviceName) {
		$methodName = 'is' . strtolower((string) $deviceName);

		return $this->mobileDetector->$methodName();
	}

	/**
	 * Is iOS
	 * @return boolean
	 */
	public function isIOS() {
		return $this->mobileDetector->isIOS();
	}

	/**
	 * Is Android OS
	 * @return boolean
	 */
	public function isAndroidOS() {
		return $this->mobileDetector->isAndroidOS();
	}

	/**
	 * Extension name
	 * @return string
	 */
	public function getName() {
		return 'mobile_detect.twig.extension';
	}

}
