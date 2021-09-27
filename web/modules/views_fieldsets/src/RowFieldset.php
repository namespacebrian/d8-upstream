<?php

namespace Drupal\views_fieldsets;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Render\Markup;
use Drupal\views\ResultRow;
use Drupal\views_fieldsets\Plugin\views\field\Fieldset;

/**
 * {@inheritdoc}
 */
class RowFieldset {

  public $row;

  public $properties = [];

  public $children = [];

  /**
   * Constructs an RowFieldset object.
   */
  public function __construct($field, ResultRow $row) {
    $this->row = $row;
    $this->properties = get_object_vars($field);
  }

  /**
   * Magic method: __isset a property value.
   *
   * @param string $name
   *   Method's name.
   */
  public function __isset($name) {
    return TRUE;
  }

  /**
   * Magic method: Gets a property value.
   *
   * @param string $name
   *   Method's name.
   */
  public function __get($name) {
    $method_name = 'get' . Unicode::ucwords($name);
    if (is_callable($method = [$this, $method_name])) {
      return call_user_func($method);
    }
    if (!empty($name) && !empty($this->properties[$name])) {
      return $this->properties[$name];
    }
    return FALSE;
  }

  /**
   * Object getcontent().
   */
  public function getContent() {
    return $this->render();
  }

  /**
   * Object getwrapperelement().
   */
  public function getWrapperelement() {
    return '';
  }

  /**
   * Object getelementtype().
   */
  public function getElementtype() {
    return '';
  }

  /**
   * Object render().
   */
  public function render() {
    // @todo Theme hook suggestions!
    $element = [
      '#theme' => 'views_fieldsets_' . $this->getWrapperType(),
      '#fields' => $this->children,
      '#legend' => Markup::create($this->getLegend()),
      '#collapsible' => (bool) $this->handler->options['collapsible'],
      '#attributes' => [
        'class' => $this->getClasses(),
      ],
    ];
    if ($this->handler->options['collapsed'] && $this->getWrapperType() != 'div') {
      $element['#attributes']['class'][] = 'collapsed';
    }

    return render($element);
  }

  /**
   * Object getWrapperType().
   */
  protected function getWrapperType() {
    $allowed = Fieldset::getWrapperTypes();
    $wrapper = $this->handler->options['wrapper'];
    if (isset($allowed[$wrapper])) {
      return $wrapper;
    }

    reset($allowed);
    return key($allowed);
  }

  /**
   * Object getLegend().
   */
  protected function getLegend() {
    return $this->tokenize($this->handler->options['legend']);
  }

  /**
   * Object getClasses().
   */
  protected function getClasses() {
    $classes = explode('  ', $this->handler->options['classes']);
    $classes = array_map(function ($class) {
      return Html::getClass($this->tokenize($class));
    }, $classes);

    return $classes;
  }

  /**
   * Object tokenize().
   *
   * @param string $string
   *   String.
   */
  protected function tokenize($string) {
    return $this->handler->tokenizeValue($string, $this->row->index);
  }

  /**
   * Object addChild().
   *
   * @param array $fields
   *   Fields.
   * @param string $field_name
   *   Field name.
   */
  public function addChild(array $fields, $field_name) {
    $this->children[$field_name] = $fields[$field_name];
  }

}
