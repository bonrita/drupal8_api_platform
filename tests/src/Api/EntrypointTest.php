<?php

namespace Drupal\Tests\api_platform\Api;


use Drupal\api_platform\Core\Api\Entrypoint;
use Drupal\api_platform\Core\Metadata\Resource\ResourceNameCollection;
use Drupal\Tests\UnitTestCase;

/**
 * Class EntrypointTest
 *
 * @package Drupal\Tests\api_platform\Api
 *
 * @coversDefaultClass \Drupal\api_platform\Core\Api\Entrypoint
 * @group api_platform
 */
class EntrypointTest extends UnitTestCase {

  public function testGetResourceNameCollection()
  {
    $resourceNameCollection = new ResourceNameCollection();
    $entrypoint = new Entrypoint($resourceNameCollection);
    $this->assertEquals($entrypoint->getResourceNameCollection(), $resourceNameCollection);
  }

}
