<?php


namespace Drupal\Tests\api_platform\Metadata\Resource\Factory;


use Doctrine\Common\Annotations\Reader;
use Drupal\api_platform\Core\Metadata\Resource\Factory\AnnotationResourceNameCollectionFactory;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\ResourceNameCollection;
use Drupal\Tests\UnitTestCase;

/**
 * Class AnnotationResourceNameCollectionFactoryTest
 *
 * @package Drupal\Tests\api_platform\Metadata\Resource\Factory
 * @coversDefaultClass \Drupal\api_platform\Core\Metadata\Resource\Factory\AnnotationResourceNameCollectionFactory
 */
class AnnotationResourceNameCollectionFactoryTest extends UnitTestCase {

  public function testCreate()
  {
    $decorated = $this->prophesize(ResourceNameCollectionFactoryInterface::class);
    $decorated->create()->willReturn(new ResourceNameCollection(['foo', 'bar']))->shouldBeCalled();

    $reader = $this->prophesize(Reader::class);

    $metadata = new AnnotationResourceNameCollectionFactory($reader->reveal(), [], $decorated->reveal());

    $this->assertEquals(new ResourceNameCollection(['foo', 'bar']), $metadata->create());
  }

}
