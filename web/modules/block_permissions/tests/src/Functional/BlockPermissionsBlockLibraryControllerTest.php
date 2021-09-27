<?php

namespace Drupal\Tests\block_permissions\Functional;

use Drupal\Core\Url;

/**
 * Tests Block permissions block library controller.
 *
 * @coversDefaultClass \Drupal\block_permissions\Controller\BlockPermissionsBlockLibraryController
 *
 * @group block_permissions
 */
class BlockPermissionsBlockLibraryControllerTest extends BlockPermissionsBrowserTestBase {

  /**
   * User with permissions to administer core blocks.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $coreBlocksUser;

  /**
   * User with permissions to administer system module blocks.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $systemBlocksUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->coreBlocksUser = $this->drupalCreateUser([
      'administer blocks',
      'administer blocks provided by core',
      "administer block settings for theme $this->defaultTheme",
    ]);
    $this->systemBlocksUser = $this->drupalCreateUser([
      'administer blocks',
      'administer blocks provided by system',
      "administer block settings for theme $this->secondTheme",
    ]);
  }

  /**
   * Tests blocks visibility on the "/admin/structure/block/library/{theme}".
   *
   * @covers ::listBlocks
   */
  public function testListBlocks() {
    $page_title_block_definition = \Drupal::service('plugin.manager.block')->getDefinition('page_title_block');
    $system_branding_block_definition = \Drupal::service('plugin.manager.block')->getDefinition('system_branding_block');

    // Ensure that the user has the access to the library page of the theme
    // which blocks it is able to manage and block is visible.
    $this->drupalLogin($this->coreBlocksUser);
    $this->drupalGet($this->getBlockAdminLibraryThemeUrl($this->defaultTheme));
    $this->assertBlockLibraryPageHasAccess();
    $this->assertSession()->pageTextContains($page_title_block_definition['admin_label']);
    $this->assertSession()->pageTextNotContains($system_branding_block_definition['admin_label']);
    $this->drupalGet($this->getBlockAdminLibraryThemeUrl($this->secondTheme));
    $this->assertSession()->statusCodeEquals(403);

    // Ensure the same for the another user.
    $this->drupalLogin($this->systemBlocksUser);
    $this->drupalGet($this->getBlockAdminLibraryThemeUrl($this->defaultTheme));
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet($this->getBlockAdminLibraryThemeUrl($this->secondTheme));
    $this->assertBlockLibraryPageHasAccess();
    $this->assertSession()->pageTextNotContains($page_title_block_definition['admin_label']);
    $this->assertSession()->pageTextContains($system_branding_block_definition['admin_label']);
  }

  /**
   * Asserts that the user has the access to the block library page.
   */
  protected function assertBlockLibraryPageHasAccess() {
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Place block');
  }

  /**
   * Gets the URL of the block library page.
   *
   * @param string $theme
   *   Theme name.
   *
   * @return \Drupal\Core\Url
   *   A new Url object for a routed URL.
   */
  protected function getBlockAdminLibraryThemeUrl($theme) {
    return Url::fromRoute('block.admin_library', [
      'theme' => $theme,
    ]);
  }

}
