<?php

namespace Drupal\Tests\block_permissions\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Base class for block_permissions tests.
 */
abstract class BlockPermissionsBrowserTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'block_permissions',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Second theme.
   *
   * @var string
   */
  protected $secondTheme = 'bartik';

  /**
   * Page title block.
   *
   * @var \Drupal\block\BlockInterface
   */
  protected $pageTitleBlock;

  /**
   * System branding block.
   *
   * @var \Drupal\block\BlockInterface
   */
  protected $systemBrandingBlock;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Enable second theme.
    \Drupal::service('theme_installer')->install([$this->secondTheme], TRUE);

    // Add blocks to default theme.
    $this->pageTitleBlock = $this->drupalPlaceBlock('page_title_block');
    $this->systemBrandingBlock = $this->drupalPlaceBlock('system_branding_block');
  }

}
