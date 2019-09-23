<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\api_platform\Core\Exception\InvalidArgumentException;

/**
 * Class ResourceClassResolver
 *
 * @package Drupal\api_platform\Core\Api
 */
class ResourceClassResolver implements ResourceClassResolverInterface {

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
    // TODO: Implement isResourceClass() method.
  }

}
