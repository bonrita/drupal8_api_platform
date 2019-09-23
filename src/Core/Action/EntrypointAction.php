<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Action;

use Drupal\api_platform\Core\Api\Entrypoint;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;

/**
 * Class EntrypointAction
 *
 * @package Drupal\api_platform\Core\Action
 *
 *  Generates the API entrypoint.
 */
final class EntrypointAction {

  private $resourceNameCollectionFactory;

  public function __construct(ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory)
  {
    $this->resourceNameCollectionFactory = $resourceNameCollectionFactory;
  }

  public function __invoke() {
//    $build['content'] = [
//      '#markup' => 'Trying this funny route',
//    ];
//
//    return $build;

    return new Entrypoint($this->resourceNameCollectionFactory->create());

  }

}
