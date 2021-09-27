<?php

namespace Drupal\block_region_permissions\Routing;

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
    // Change access callback for the block edit and delete forms.
    $routeNames = [
      'entity.block.edit_form',
      'entity.block.delete_form',
    ];
    foreach ($routeNames as $name) {
      if ($route = $collection->get($name)) {
        $route->addRequirements([
          '_custom_access' => 'Drupal\block_region_permissions\AccessControlHandler::blockFormAccess',
        ]);
      }
    }
  }

}
