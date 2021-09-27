<?php

namespace Drupal\Tests\entity_clone\Functional;

use Drupal\node\Entity\Node;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;
use Drupal\Tests\node\Functional\NodeTestBase;

/**
 * Create a content and test a clone.
 *
 * @group entity_clone
 */
class EntityCloneContentTest extends NodeTestBase {

  use EntityReferenceTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['entity_clone', 'block', 'node', 'datetime', 'taxonomy'];

  /**
   * Theme to enable by default
   * @var string
   */
  protected $defaultTheme = 'classy';

  /**
   * Permissions to grant admin user.
   *
   * @var array
   */
  protected $permissions = [
    'bypass node access',
    'administer nodes',
    'clone node entity',
  ];

  /**
   * A user with permission to bypass content access checks.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Sets the test up.
   */
  protected function setUp(): void {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser($this->permissions);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test content entity clone.
   */
  public function testContentEntityClone() {
    $node_title = $this->randomMachineName(8);
    $node = Node::create([
      'type' => 'page',
      'title' => $node_title,
    ]);
    $node->save();

    $this->drupalPostForm('entity_clone/node/' . $node->id(), [], t('Clone'));

    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'title' => $node_title . ' - Cloned',
      ]);
    $node = reset($nodes);
    $this->assertInstanceOf(Node::class, $node, 'Test node cloned found in database.');
  }

  public function testContentReferenceConfigEntity() {
    $this->createEntityReferenceField('node', 'page', 'config_field_reference', 'Config field reference', 'taxonomy_vocabulary');

    $node_title = $this->randomMachineName(8);
    $node = Node::create([
      'type' => 'page',
      'title' => $node_title,
      'config_field_reference' => 'tags'
    ]);
    $node->save();

    $this->drupalGet('entity_clone/node/' . $node->id());
    $this->assertSession()->elementNotExists('css', '#edit-recursive-nodepageconfig-field-reference');
  }

}
