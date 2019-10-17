<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Property;

use Traversable;

/**
 * A collection of property names for a given resource.
 *
 * Class PropertyNameCollection
 *
 * @package Drupal\api_platform\Core\Metadata\Property
 */
final class PropertyNameCollection implements \IteratorAggregate, \Countable {

  /**
   * @var string[]
   */
  private $properties;

  /**
   * @param string[] $properties
   */
  public function __construct(array $properties = [])
  {
    $this->properties = $properties;
  }

  /**
   * @inheritDoc
   */
  public function getIterator() {
    return new \ArrayIterator($this->properties);
  }

  /**
   * @inheritDoc
   */
  public function count() {
    return \count($this->properties);
  }

}
