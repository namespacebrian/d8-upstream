<?php

namespace Drupal\Tests\book\Unit;

use Drupal\book\BookManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @coversDefaultClass \Drupal\book\BookManager
 * @group book
 */
class BookManagerTest extends UnitTestCase {

  /**
   * The mocked entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The mocked config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The mocked translation manager.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $translation;

  /**
   * The mocked renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $renderer;

  /**
   * The tested book manager.
   *
   * @var \Drupal\book\BookManager
   */
  protected $bookManager;

  /**
   * Book outline storage.
   *
   * @var \Drupal\book\BookOutlineStorageInterface
   */
  protected $bookOutlineStorage;

  /**
   * The mocked form state
   *
   * @var \Drupal\Core\Form\FormState
   */
  protected $form_state;

  /**
   * The mocked node Interface.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * The mocked User
   *
   * @var \Drupal\user\Entity\User
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $book_id = [
      'nid' => 'new',
      'has_children' => 0,
      'original_bid' => 0,
      'parent_depth_limit' => 0,
      'bid' => 0,
      'pid' => 0,
      'weight' => 0,
      'parent_depth_limit' => 8,
      'options' => [],
    ];
    $this->form_state = $this->getMockBuilder('Drupal\Core\Form\FormState')
      ->disableOriginalConstructor()->disableOriginalConstructor()
      ->setMethods(['hasValue', 'getValue'])->getMock();
    $this->form_state->expects($this->any())
      ->method('getValue')
      ->willReturn($book_id);
    $this->node = $this->getMockBuilder('Drupal\node\NodeInterface')
      ->disableOriginalConstructor()->getMock();
    $this->node->book = $book_id;
    $this->account = $this->getMockBuilder('Drupal\user\Entity\User')
      ->disableOriginalConstructor()->getMock();
    $this->translation = $this->getStringTranslationStub();
    $config = [
      'book.settings' => [
        'allowed_types' => [
          'page'
        ]
      ]
    ];
    $this->configFactory = $this->getConfigFactoryStub($config);
    $this->bookOutlineStorage = $this->createMock('Drupal\book\BookOutlineStorageInterface');
    $this->renderer = $this->createMock('\Drupal\Core\Render\RendererInterface');
    $container = new ContainerBuilder();
    $container->set('config.factory', $this->configFactory);
    \Drupal::setContainer($container);
    $this->bookManager = new BookManager($this->entityTypeManager, $this->translation, $this->configFactory, $this->bookOutlineStorage, $this->renderer);
  }

  /**
   * Tests the getBookParents() method.
   *
   * @dataProvider providerTestGetBookParents
   */
  public function testGetBookParents($book, $parent, $expected) {
    $this->assertEquals($expected, $this->bookManager->getBookParents($book, $parent));
  }

  /**
   * Provides test data for testGetBookParents.
   *
   * @return array
   *   The test data.
   */
  public function providerTestGetBookParents() {
    $empty = [
      'p1' => 0,
      'p2' => 0,
      'p3' => 0,
      'p4' => 0,
      'p5' => 0,
      'p6' => 0,
      'p7' => 0,
      'p8' => 0,
      'p9' => 0,
    ];
    return [
      // Provides a book without an existing parent.
      [
        ['pid' => 0, 'nid' => 12],
        [],
        ['depth' => 1, 'p1' => 12] + $empty,
      ],
      // Provides a book with an existing parent.
      [
        ['pid' => 11, 'nid' => 12],
        ['nid' => 11, 'depth' => 1, 'p1' => 11],
        ['depth' => 2, 'p1' => 11, 'p2' => 12] + $empty,
      ],
      // Provides a book with two existing parents.
      [
        ['pid' => 11, 'nid' => 12],
        ['nid' => 11, 'depth' => 2, 'p1' => 10, 'p2' => 11],
        ['depth' => 3, 'p1' => 10, 'p2' => 11, 'p3' => 12] + $empty,
      ],
    ];
  }

  /**
   * Testing the Book Outline form element in node add page form.
   * When the Book setting is enabled for the Content Type 'Page'.
   */
  public function testAddFormElementsNodeAddWithBook() {
    $form = [];
    $this->node->expects($this->any())
      ->method('getType')
      ->willReturn('page');
    $addform = $this->bookManager->addFormElements($form, $this->form_state, $this->node, $this->account);
    $this->assertArrayHasKey('book', $addform);
  }

  /**
   * Testing the Book Outline form element in node add article form.
   * When the Book setting is not enabled for the Content Type 'Article'
   */
  public function testAddFormElementsNodeAddWithoutBook() {
    $form = [];
    $this->node->expects($this->any())
      ->method('getType')
      ->willReturn('artilce');
    $addform = $this->bookManager->addFormElements($form, $this->form_state, $this->node, $this->account);
    $this->assertArrayNotHasKey('book', $addform);
  }

}
