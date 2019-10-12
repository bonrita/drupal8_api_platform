<?php

declare(strict_types=1);

namespace Drupal\Tests\api_platform\Api;

use Drupal\api_platform\Core\Api\FilterCollection;
use Drupal\Tests\UnitTestCase;

/**
 * Class FilterCollectionTest
 *
 * @package Drupal\Tests\api_platform\Api
 *
 * @coversDefaultClass \Drupal\api_platform\Core\Api\FilterCollection
 * @group api_platform
 */
class FilterCollectionTest extends UnitTestCase
{
    /**
     * @group legacy
     * @expectedDeprecation The Drupal\api_platform\Core\Api\FilterCollection class is deprecated since version 2.1 and will be removed in 3.0. Provide an implementation of Psr\Container\ContainerInterface instead.
     */
    public function testIsArrayObject()
    {
        $filterCollection = new FilterCollection();
        $this->assertInstanceOf(\ArrayObject::class, $filterCollection);
    }

}
