<?php

namespace Drupal\Tests\block_permissions\Functional;

use Drupal\Core\Url;

/**
 * Tests Block permissions access control handler for block list pages.
 *
 * @coversDefaultClass \Drupal\block_permissions\BlockPermissionsAccessControlHandler
 *
 * @group block_permissions
 */
class BlockListsRoutesAccessTest extends BlockPermissionsBrowserTestBase {

  /**
   * User with permissions to administer blocks in the default theme.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $defaultThemeUser;

  /**
   * User with permissions to administer blocks in the second theme.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $secondThemeUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->defaultThemeUser = $this->drupalCreateUser([
      'administer blocks',
      "administer block settings for theme $this->defaultTheme",
    ]);
    $this->secondThemeUser = $this->drupalCreateUser([
      'administer blocks',
      "administer block settings for theme $this->secondTheme",
    ]);
  }

  /**
   * Test the access to the "/admin/structure/block" page.
   *
   * @covers ::blockListAccess
   */
  public function testBlockListAccess() {
    $block_admin_display_path = Url::fromRoute('block.admin_display');

    // Ensure the user has the access to the list of blocks of the default
    // theme.
    $this->drupalLogin($this->defaultThemeUser);
    $this->drupalGet($block_admin_display_path);
    $this->assertBlockListPageHasAccess();

    // Ensure the user doesn't have the access to the list of blocks of the
    // default theme.
    $this->drupalLogin($this->secondThemeUser);
    $this->drupalGet($block_admin_display_path);
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Test the access to the "/admin/structure/block/list/{theme}" page.
   *
   * @covers ::blockThemeListAccess
   */
  public function testBlockThemeListAccess() {
    // Ensure the user has the access to the list of blocks of the default theme
    // only.
    $this->drupalLogin($this->defaultThemeUser);
    $this->drupalGet($this->getBlockAdminDisplayThemeUrl($this->defaultTheme));
    $this->assertBlockListPageHasAccess();
    $this->drupalGet($this->getBlockAdminDisplayThemeUrl($this->secondTheme));
    $this->assertSession()->statusCodeEquals(403);

    // Ensure the user has the access to the list of blocks of the second theme
    // only.
    $this->drupalLogin($this->secondThemeUser);
    $this->drupalGet($this->getBlockAdminDisplayThemeUrl($this->defaultTheme));
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet($this->getBlockAdminDisplayThemeUrl($this->secondTheme));
    $this->assertBlockListPageHasAccess();
  }

  /**
   * Gets the URL of the block list page.
   *
   * @param string $theme
   *   Theme name.
   *
   * @return \Drupal\Core\Url
   *   A new Url object for a routed URL.
   */
  protected function getBlockAdminDisplayThemeUrl($theme) {
    return Url::fromRoute('block.admin_display_theme', [
      'theme' => $theme,
    ]);
  }

  /**
   * Asserts that the user has the access to the block list page.
   */
  protected function assertBlockListPageHasAccess() {
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Block layout');
  }

}
