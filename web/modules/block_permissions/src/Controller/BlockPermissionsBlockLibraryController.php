<?php

namespace Drupal\block_permissions\Controller;

use Drupal\block\Controller\BlockLibraryController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for changes in the BlockLibraryController.
 */
class BlockPermissionsBlockLibraryController extends BlockLibraryController {

  /**
   * {@inheritdoc}
   */
  public function listBlocks(Request $request, $theme) {
    // First build the list.
    $build = parent::listBlocks($request, $theme);

    // Iterate over the rows and validate each plugin.
    foreach ($build['blocks']['#rows'] as $key => $row) {
      if (!empty($row['operations']['data']['#links']['add']['url'])) {
        // Get the plugin via the add URL (there is no other way..).
        $url = $row['operations']['data']['#links']['add']['url'];

        if (!$url->access()) {
          unset($build['blocks']['#rows'][$key]);
        }
      }
    }

    return $build;
  }

}
