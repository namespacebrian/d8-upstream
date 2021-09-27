<?php

namespace Drupal\block_permissions;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Block\BlockManagerInterface;

/**
 * Provides dynamic permissions of the blocks module.
 */
class BlockPermissionsPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Constructs a new BlockPermissionsPermissions instance.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   */
  public function __construct(BlockManagerInterface $block_manager, ThemeHandlerInterface $theme_handler) {
    $this->blockManager = $block_manager;
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('theme_handler')
    );
  }

  /**
   * Return the permissions for the block permissions.
   *
   * @return array
   *   Array of permissions.
   */
  public function permissions() {
    $permissions = [];

    // Get a list of available themes and generate a permission for block
    // administration per theme.
    $themes = $this->themeHandler->listInfo();
    foreach ($themes as $key => $theme) {
      if ($theme->status == 1 && (!isset($theme->info['hidden']) || $theme->info['hidden'] != 1)) {
        $permissions['administer block settings for theme ' . $key] = [
          'title' => $this->t('Administer block settings for the theme @label', ['@label' => ucfirst($theme->getName())]),
          'description' => $this->t('This permission refines the administer blocks permission.'),
        ];
      }
    }

    // Create a permission for each block category.
    $definitions = $this->blockManager->getDefinitions();
    $providers = [];
    foreach ($definitions as $definition) {
      $providers[$definition['provider']] = $definition['provider'];
    }
    foreach ($providers as $provider) {
      $permissions['administer blocks provided by ' . $provider] = [
        'title' => $this->t('Manage blocks provided by @label', ['@label' => $provider]),
        'description' => $this->t('When not given, the user cannot manage blocks provided by this provider.'),
      ];
    }

    return $permissions;
  }

}
