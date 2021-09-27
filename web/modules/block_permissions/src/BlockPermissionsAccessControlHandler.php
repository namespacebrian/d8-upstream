<?php

namespace Drupal\block_permissions;

use Drupal\block\Entity\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the block permissions.
 */
class BlockPermissionsAccessControlHandler implements ContainerInjectionInterface {

  /**
   * The condition plugin manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('current_user'),
      $container->get('config.factory'),
    );

  }

  /**
   * Constructs the block access control handler instance.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   Plugin manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(BlockManagerInterface $block_manager, AccountInterface $current_user, ConfigFactoryInterface $config_factory) {
    $this->blockManager = $block_manager;
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
  }

  /**
   * Access check for the default block list manage page.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   AccessResult object.
   */
  public function blockListAccess() {
    $theme = $this->configFactory->get('system.theme')->get('default');

    // Check if the user has the proper permissions.
    $access = AccessResult::allowedIfHasPermission($this->currentUser, 'administer block settings for theme ' . $theme);

    return $access;
  }

  /**
   * Access check for the block list for specific themes.
   *
   * @param string $theme
   *   The theme name.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   An access result.
   */
  public function blockThemeListAccess($theme) {
    // Check if the user has the proper permissions.
    $access = AccessResult::allowedIfHasPermission($this->currentUser, 'administer block settings for theme ' . $theme);

    return $access;
  }

  /**
   * Access check for the add block form.
   *
   * @param string $plugin_id
   *   The plugin name.
   * @param string $theme
   *   The theme name.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   An access result.
   */
  public function blockAddFormAccess($plugin_id, $theme) {
    $plugin = $this->blockManager->getDefinition($plugin_id);

    // Check if the user has the proper permissions.
    $access = AccessResult::allowedIfHasPermissions($this->currentUser, [
      'administer blocks provided by ' . $plugin['provider'],
      'administer block settings for theme ' . $theme,
    ]);

    return $access;
  }

  /**
   * Access check for the block config form.
   *
   * @param \Drupal\block\Entity\Block $block
   *   The theme name.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   An access result.
   */
  public function blockFormAccess(Block $block) {
    $plugin = $block->getPlugin();
    $configuration = $plugin->getConfiguration();

    // Check if the user has the proper permissions.
    $access = AccessResult::allowedIfHasPermission($this->currentUser, 'administer blocks provided by ' . $configuration['provider']);

    return $access;
  }

}
