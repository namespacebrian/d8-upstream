<?php

namespace Drupal\block_region_permissions;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides dynamic permissions for the block region permissions module.
 */
class Permissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Constructs a new Permissions instance.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   */
  public function __construct(ThemeHandlerInterface $theme_handler) {
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('theme_handler')
    );
  }

  /**
   * Get permissions.
   *
   * @return array
   *   Array of permissions.
   */
  public function get() {
    $permissions = [];

    // Get themes and generate permissions for each theme's regions.
    $themes = $this->themeHandler->listInfo();
    foreach ($themes as $theme_key => $theme) {
      if ($theme->status == 1 && (!isset($theme->info['hidden']) || $theme->info['hidden'] != 1)) {
        $theme_name = $theme->info['name'];
        // Get regions for this theme.
        $regions = $theme->info['regions'];
        // Add permissions for each region.
        foreach ($regions as $region_key => $region_name) {
          $permissions["administer $theme_key $region_key"] = [
            'title' => $this->t('Administer: <em>@theme</em> - <em>@region</em>', ['@theme' => $theme_name, '@region' => $region_name]),
          ];
        }
      }
    }
    return $permissions;
  }

}
