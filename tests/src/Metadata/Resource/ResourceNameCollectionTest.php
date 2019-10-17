<?php


namespace Drupal\Tests\api_platform\Metadata\Resource;


use Drupal\api_platform\Core\Metadata\Resource\ResourceNameCollection;
use Drupal\Tests\UnitTestCase;

/**
 * Class ResourceNameCollectionTest
 *
 * @package Drupal\Tests\api_platform\Metadata\Resource
 * @coversDefaultClass \Drupal\api_platform\Core\Metadata\Resource\ResourceNameCollection
 */
class ResourceNameCollectionTest extends UnitTestCase {

  public function testValueObject()
  {
    $collection = new ResourceNameCollection(['foo', 'bar']);

    $this->assertInstanceOf(\Countable::class, $collection);
    $this->assertInstanceOf(\IteratorAggregate::class, $collection);
    $this->assertCount(2, $collection);
    $this->assertInstanceOf(\ArrayIterator::class, $collection->getIterator());
  }

}
