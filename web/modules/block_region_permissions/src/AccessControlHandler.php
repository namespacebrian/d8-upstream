<?php

namespace Drupal\block_region_permissions;

use Drupal\block\Entity\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the block region permissions.
 */
class AccessControlHandler implements ContainerInjectionInterface {

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block')
    );
  }

  /**
   * Returns the service container.
   *
   * This method is marked private to prevent sub-classes from retrieving
   * services from the container through it. Instead,
   * \Drupal\Core\DependencyInjection\ContainerInjectionInterface should be used
   * for injecting services.
   *
   * @return \Symfony\Component\DependencyInjection\ContainerInterface
   *   The service container.
   */
  private function container() {
    return \Drupal::getContainer();
  }

  /**
   * Returns the current user.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The current user.
   */
  protected function currentUser() {
    if (!$this->currentUser) {
      $this->currentUser = $this->container()->get('current_user');
    }
    return $this->currentUser;
  }

  /**
   * Access check for the block edit and delete forms.
   *
   * @param \Drupal\block\Entity\Block $block
   *   The block.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   An access result.
   */
  public function blockFormAccess(Block $block) {
    $account = $this->currentUser();
    $theme = $block->get('theme');
    $region = $block->get('region');
    // Check if the user has the proper permissions.
    $access = AccessResult::allowedIfHasPermission($account, "administer $theme $region");

    return $access;
  }

}
