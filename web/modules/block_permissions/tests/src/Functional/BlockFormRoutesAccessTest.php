<?php

namespace Drupal\Tests\block_permissions\Functional;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Url;

/**
 * Tests Block permissions access control handler for block configuration pages.
 *
 * @coversDefaultClass \Drupal\block_permissions\BlockPermissionsAccessControlHandler
 *
 * @group block_permissions
 */
class BlockFormRoutesAccessTest extends BlockPermissionsBrowserTestBase {

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
   * User with permissions to administer blocks in second theme.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $secondThemeUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // User can administer blocks from core.
    $this->coreBlocksUser = $this->drupalCreateUser([
      'administer blocks',
      "administer block settings for theme $this->defaultTheme",
      'administer blocks provided by core',
    ]);

    // User can administer blocks from system module.
    $this->systemBlocksUser = $this->drupalCreateUser([
      'administer blocks',
      "administer block settings for theme $this->defaultTheme",
      'administer blocks provided by system',
    ]);

    // User can administer blocks from core but in second theme only.
    $this->secondThemeUser = $this->drupalCreateUser([
      'administer blocks',
      "administer block settings for theme $this->secondTheme",
      'administer blocks provided by core',
      'administer blocks provided by system',
    ]);
  }

  /**
   * Tests access to "/admin/structure/block/add/{plugin_id}/{theme}" page.
   *
   * @covers ::blockAddFormAccess
   */
  public function testBlockAddFormAccess() {
    // Ensure that the user with permissions to administer blocks in the default
    // theme can create core's blocks only.
    $this->drupalLogin($this->coreBlocksUser);
    $this->drupalGet($this->getBlockAdminAddUrl('page_title_block', $this->defaultTheme));
    $this->assertBlockFormPageHasAccess();
    $this->drupalGet($this->getBlockAdminAddUrl('system_branding_block', $this->defaultTheme));
    $this->assertSession()->statusCodeEquals(403);

    // Ensure that the user with permissions to administer blocks in the default
    // theme can create system's blocks only.
    $this->drupalLogin($this->systemBlocksUser);
    $this->drupalGet($this->getBlockAdminAddUrl('page_title_block', $this->defaultTheme));
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet($this->getBlockAdminAddUrl('system_branding_block', $this->defaultTheme));
    $this->assertBlockFormPageHasAccess();

    // Ensure that the user can add blocks only to a theme where it can
    // administer blocks.
    $this->drupalLogin($this->secondThemeUser);
    $this->drupalGet($this->getBlockAdminAddUrl('page_title_block', $this->secondTheme));
    $this->assertBlockFormPageHasAccess();
    $this->drupalGet($this->getBlockAdminAddUrl('system_branding_block', $this->secondTheme));
    $this->assertBlockFormPageHasAccess();
    $this->drupalGet($this->getBlockAdminAddUrl('page_title_block', $this->defaultTheme));
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet($this->getBlockAdminAddUrl('system_branding_block', $this->defaultTheme));
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Tests the access to the block edit page.
   *
   * Path to test:
   * "/admin/structure/block/manage/{block}",
   *
   * $covers ::blockFormAccess
   */
  public function testBlockEditFormAccess() {
    $page_title_block_edit = $this->getBlockEditFormUrl($this->pageTitleBlock->id());
    $system_branding_block_edit = $this->getBlockEditFormUrl($this->systemBrandingBlock->id());

    // Ensure user can edit block from core only.
    $this->drupalLogin($this->coreBlocksUser);
    $this->drupalGet($page_title_block_edit);
    $this->assertBlockFormPageHasAccess();
    $this->drupalGet($system_branding_block_edit);
    $this->assertSession()->statusCodeEquals(403);

    // Ensure user can edit block from system module only.
    $this->drupalLogin($this->systemBlocksUser);
    $this->drupalGet($page_title_block_edit);
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet($system_branding_block_edit);
    $this->assertBlockFormPageHasAccess();
  }

  /**
   * Tests the access to the block delete page.
   *
   * Path to test:
   * "/admin/structure/block/manage/{block}/delete".
   *
   * $covers ::blockFormAccess
   */
  public function testBlockDeleteFormAccess() {
    $page_title_block_delete = $this->getBlockDeleteFormUrl($this->pageTitleBlock->id());
    $system_branding_block_delete = $this->getBlockDeleteFormUrl($this->systemBrandingBlock->id());

    // Ensure user can delete block from core only.
    $this->drupalLogin($this->coreBlocksUser);
    $this->drupalGet($page_title_block_delete);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($this->getBlockDeleteFormTitle($this->pageTitleBlock->label()));
    $this->drupalGet($system_branding_block_delete);
    $this->assertSession()->statusCodeEquals(403);

    // Ensure user can delete block from system module only.
    $this->drupalLogin($this->systemBlocksUser);
    $this->drupalGet($page_title_block_delete);
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet($system_branding_block_delete);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($this->getBlockDeleteFormTitle($this->systemBrandingBlock->label()));
  }

  /**
   * Asserts that user has the access to the page.
   */
  protected function assertBlockFormPageHasAccess() {
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Configure block');
  }

  /**
   * Gets the path to the block edit form.
   *
   * @param string $id
   *   Block id.
   *
   * @return \Drupal\Core\Url
   *   A new Url object for a routed URL.
   */
  protected function getBlockEditFormUrl($id) {
    return Url::fromRoute('entity.block.edit_form', [
      'block' => $id,
    ]);
  }

  /**
   * Gets the path to the block delete form.
   *
   * @param string $id
   *   Block id.
   *
   * @return \Drupal\Core\Url
   *   A new Url object for a routed URL.
   */
  protected function getBlockDeleteFormUrl($id) {
    return Url::fromRoute('entity.block.delete_form', [
      'block' => $id,
    ]);
  }

  /**
   * Gets the path to block add form.
   *
   * @param string $id
   *   Block id.
   * @param string $theme
   *   Theme name.
   *
   * @return \Drupal\Core\Url
   *   A new Url object for a routed URL.
   */
  protected function getBlockAdminAddUrl($id, $theme) {
    return Url::fromRoute('block.admin_add', [
      'plugin_id' => $id,
      'theme' => $theme,
    ]);
  }

  /**
   * Gets the title of the block deletion page.
   *
   * @param string $name
   *   Block name.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   Page title.
   */
  protected function getBlockDeleteFormTitle($name) {
    return new FormattableMarkup('Are you sure you want to remove the block @name?', ['@name' => $name]);
  }

}
