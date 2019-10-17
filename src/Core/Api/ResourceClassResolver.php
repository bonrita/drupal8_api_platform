<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\api_platform\Core\Exception\InvalidArgumentException;

/**
 * Class ResourceClassResolver
 *
 * @package Drupal\api_platform\Core\Api
 */
class ResourceClassResolver implements ResourceClassResolverInterface {

  private $resourceNameCollectionFactory;
  private $localIsResourceClassCache = [];

  public function __construct(ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory)
  {
    $this->resourceNameCollectionFactory = $resourceNameCollectionFactory;
  }

  /**
   * @inheritDoc
   */
  public function getResourceClass(
    $value,
    string $resourceClass = NULL,
    bool $strict = FALSE
  ): string {
    // TODO: Implement getResourceClass() method.
  }

  /**
   * @inheritDoc
   */
  public function isResourceClass(string $type): bool {
    if (isset($this->localIsResourceClassCache[$type])) {
      return $this->localIsResourceClassCache[$type];
    }

    foreach ($this->resourceNameCollectionFactory->create() as $resourceClass) {
      if (is_a($type, $resourceClass, true)) {
        return $this->localIsResourceClassCache[$type] = true;
      }
    }

    return $this->localIsResourceClassCache[$type] = false;
  }

}
