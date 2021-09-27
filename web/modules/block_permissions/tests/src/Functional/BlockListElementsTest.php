<?php

namespace Drupal\Tests\block_permissions\Functional;

use Drupal\Core\Url;

/**
 * Tests visibility of configuration elements on the block_admin_display_form.
 *
 * @group block_permissions
 */
class BlockListElementsTest extends BlockPermissionsBrowserTestBase {

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
  }

  /**
   * Tests configuration elements on the block list page.
   *
   * Page for testing - "/admin/structure/block".
   */
  public function testBlockListPage() {
    $block_admin_display_path = Url::fromRoute('block.admin_display');

    // Ensure user sees blocks but can administer block from core only.
    $this->drupalLogin($this->coreBlocksUser);
    $this->drupalGet($block_admin_display_path);
    $this->assertSession()->pageTextContains($this->pageTitleBlock->label());
    $this->assertSession()->pageTextContains($this->systemBrandingBlock->label());
    $this->assertBlockElementsExists($this->pageTitleBlock->id());
    $this->assertBlockElementsNotExists($this->systemBrandingBlock->id());

    // Ensure user sees blocks but can administer block from system module only.
    $this->drupalLogin($this->systemBlocksUser);
    $this->drupalGet($block_admin_display_path);
    $this->assertSession()->pageTextContains($this->pageTitleBlock->label());
    $this->assertSession()->pageTextContains($this->systemBrandingBlock->label());
    $this->assertBlockElementsNotExists($this->pageTitleBlock->id());
    $this->assertBlockElementsExists($this->systemBrandingBlock->id());
  }

  /**
   * Asserts that block configuration elements exist.
   *
   * @param string $id
   *   Block id.
   */
  protected function assertBlockElementsExists($id) {
    $row = $this->assertSession()->elementExists('css', "tr[data-drupal-selector=\"edit-blocks-$id\"]");
    // Configurations dropbutton exists.
    $this->assertSession()->elementExists('css', 'ul.dropbutton', $row);
    // Weight selector exists.
    $this->assertSession()->elementExists('css', 'select.block-weight', $row);
    // Region selector exists.
    $this->assertSession()->elementExists('css', 'select.block-region-select', $row);
    // Draggable element exists.
    $this->assertStringContainsString('draggable', $row->getAttribute('class'));
  }

  /**
   * Asserts that block configuration elements don't exist.
   *
   * @param string $id
   *   Block id.
   */
  protected function assertBlockElementsNotExists($id) {
    $row = $this->assertSession()->elementExists('css', "tr[data-drupal-selector=\"edit-blocks-$id\"]");
    // Configurations dropbutton doesn't exists.
    $this->assertSession()->elementNotExists('css', 'ul.dropbutton', $row);
    // Weight selector doesn't exists.
    $this->assertSession()->elementNotExists('css', 'select.block-weight', $row);
    // Region selector doesn't exist.
    $this->assertSession()->elementNotExists('css', 'select.block-region-select', $row);
    // Draggable element doesn't exists.
    $this->assertStringContainsString('undraggable', $row->getAttribute('class'));
  }

}
