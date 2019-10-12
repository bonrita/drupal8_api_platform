<?php

declare(strict_types=1);

namespace Drupal\Tests\api_platform\Action;


use Drupal\api_platform\Core\Action\EntrypointAction;
use Drupal\api_platform\Core\Api\Entrypoint;
use Drupal\api_platform\Core\Metadata\Resource\ResourceNameCollection;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Class EntrypointActionTest
 *
 * @package Drupal\Tests\api_platform\Action
 *
 * @coversDefaultClass \Drupal\api_platform\Core\Action\EntrypointAction
 */
class EntrypointActionTest extends UnitTestCase {

  public function testGetEntrypoint()
  {
    $resourceNameCollectionFactoryProphecy = $this->prophesize(ResourceNameCollectionFactoryInterface::class);
    $resourceNameCollectionFactoryProphecy->create()->willReturn(new ResourceNameCollection(['dummies']));
    $entrypoint = new EntrypointAction($resourceNameCollectionFactoryProphecy->reveal());
    $this->assertEquals(new Entrypoint(new ResourceNameCollection(['dummies'])), $entrypoint());
  }

}
