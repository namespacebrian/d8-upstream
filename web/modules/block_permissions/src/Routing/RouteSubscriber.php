<?php

namespace Drupal\block_permissions\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Change access callback for the blocks list per theme to our
    // permission-per-theme based version.
    if ($route = $collection->get('block.admin_display_theme')) {
      $route->addRequirements([
        '_custom_access' => "\\Drupal\\block_permissions\\BlockPermissionsAccessControlHandler::blockThemeListAccess",
      ]);
    }

    // Change access callback for the default block management page.
    if ($route = $collection->get('block.admin_display')) {
      $route->addRequirements([
        '_custom_access' => "\\Drupal\\block_permissions\\BlockPermissionsAccessControlHandler::blockListAccess",
      ]);
    }

    // Change the access callback for the add form of a block.
    if ($route = $collection->get('block.admin_add')) {
      $route->addRequirements([
        '_custom_access' => "\\Drupal\\block_permissions\\BlockPermissionsAccessControlHandler::blockAddFormAccess",
      ]);
    }

    // Change the access callback for the edit form of a block.
    if ($route = $collection->get('entity.block.edit_form')) {
      $route->addRequirements([
        '_custom_access' => "\\Drupal\\block_permissions\\BlockPermissionsAccessControlHandler::blockFormAccess",
      ]);
    }

    // Change the access callback for the delete form of a block.
    if ($route = $collection->get('entity.block.delete_form')) {
      $route->addRequirements([
        '_custom_access' => "\\Drupal\\block_permissions\\BlockPermissionsAccessControlHandler::blockFormAccess",
      ]);
    }

    // Change the controller for the block admin_library.
    if ($route = $collection->get('block.admin_library')) {
      $route->setDefault('_controller', '\\Drupal\\block_permissions\\Controller\\BlockPermissionsBlockLibraryController::listBlocks');
      $route->addRequirements([
        '_custom_access' => "\\Drupal\\block_permissions\\BlockPermissionsAccessControlHandler::blockThemeListAccess",
      ]);
    }
  }

}
