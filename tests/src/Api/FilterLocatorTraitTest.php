<?php

namespace Drupal\Tests\api_platform\Api;


use Drupal\api_platform\Core\Api\FilterCollection;
use Drupal\api_platform\Core\Api\FilterInterface;
use Drupal\api_platform\Core\Api\FilterLocatorTrait;
use Drupal\Tests\UnitTestCase;
use Psr\Container\ContainerInterface;

/**
 * Class FilterLocatorTraitTest
 *
 * @package Drupal\Tests\api_platform\Api
 *
 * @coversDefaultClass \Drupal\api_platform\Core\Api\FilterLocatorTrait
 *
 * @group api_platform
 */
class FilterLocatorTraitTest extends UnitTestCase {

  public function testSetFilterLocator()
  {
    $filterLocator = $this->prophesize(ContainerInterface::class)->reveal();
    $filterLocatorTraitImpl = $this->getFilterLocatorTraitImpl();
    $filterLocatorTraitImpl->setFilterLocator($filterLocator);

    $this->assertEquals($filterLocator, $filterLocatorTraitImpl->getFilterLoator());
  }

  /**
   * @group legacy
   * @expectedDeprecation The Drupal\api_platform\Core\Api\FilterCollection class is deprecated since version 2.1 and will be removed in 3.0. Provide an implementation of Psr\Container\ContainerInterface instead.
   */
  public function testSetFilterLocatorWithDeprecatedFilterCollection()
  {
    $filterCollection = new FilterCollection();
    $filterLocatorTraitImpl = $this->getFilterLocatorTraitImpl();
    $filterLocatorTraitImpl->setFilterLocator($filterCollection);

    $this->assertEquals($filterCollection, $filterLocatorTraitImpl->getFilterLoator());
  }

//  public function testSetFilterLocatorWithNullAndNullAllowed()
//  {
//    $filterLocatorTraitImpl = $this->getFilterLocatorTraitImpl();
//    $filterLocatorTraitImpl->setFilterLocator(null, true);
//
//    $this->assertNull($filterLocatorTraitImpl->getFilterLocator());
//  }

  /**
   * @group legacy
   */
  public function testSetFilterLocatorWithNullAndNullNotAllowed()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->getExpectedExceptionMessage('The "$filterLocator" argument is expected to be an implementation of the "Psr\\Container\\ContainerInterface" interface.');

    $filterLocatorTraitImpl = $this->getFilterLocatorTraitImpl();
    $filterLocatorTraitImpl->setFilterLocator(NULL);
  }

  /**
   * @group legacy
   */
  public function testSetFilterLocatorWithInvalidFilterLocator()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The "$filterLocator" argument is expected to be an implementation of the "Psr\\Container\\ContainerInterface" interface or null.');

    $filterLocatorTraitImpl = $this->getFilterLocatorTraitImpl();
    $filterLocatorTraitImpl->setFilterLocator(new \ArrayObject(), true);
  }

  public function testGetFilter() {
    $filter = $this->prophesize(FilterInterface::class)->reveal();

    $filterLocatorProphecy = $this->prophesize(ContainerInterface::class);
    $filterLocatorProphecy->has('foo')->willReturn(true)->shouldBeCalled();
    $filterLocatorProphecy->get('foo')->willReturn($filter)->shouldBeCalled();

    $filterLocatorTraitImpl = $this->getFilterLocatorTraitImpl();
    $filterLocatorTraitImpl->setFilterLocator($filterLocatorProphecy->reveal());

    $returnedFilter = $filterLocatorTraitImpl->getFilter('foo');

    $this->assertInstanceOf(FilterInterface::class, $returnedFilter);
    $this->assertEquals($filter, $returnedFilter);

  }

  public function testGetFilterWithNonexistentFilterId()
  {
    $filterLocatorProphecy = $this->prophesize(ContainerInterface::class);
    $filterLocatorProphecy->has('foo')->willReturn(false)->shouldBeCalled();

    $filterLocatorTraitImpl = $this->getFilterLocatorTraitImpl();
    $filterLocatorTraitImpl->setFilterLocator($filterLocatorProphecy->reveal());

    $filter = $filterLocatorTraitImpl->getFilter('foo');

    $this->assertNull($filter);
  }

  private function getFilterLocatorTraitImpl()
  {
    return new class() {
      use FilterLocatorTrait {
        FilterLocatorTrait::setFilterLocator as public;
        FilterLocatorTrait::getFilter as public;
      }

      public function getFilterLoator() {
        return $this->filterLocator;
      }
    };
  }

}
