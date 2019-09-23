<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Routing;

use Drupal\api_platform\Core\Api\OperationAwareFormatsProviderInterface;
use Drupal\api_platform\Core\PathResolver\OperationPathResolverInterface;

/**
 * Class RouterOperationPathResolver
 *
 * @package Drupal\api_platform\Core\Routing
 *
 * Resolves the operations path using a Symfony route.
 */
final class RouterOperationPathResolver implements OperationPathResolverInterface, OperationAwareFormatsProviderInterface {

  /**
   * @inheritDoc
   */
  public function resolveOperationPath(
    string $resourceShortName,
    array $operation,
    $operationType
  ): string {
    // TODO: Implement resolveOperationPath() method.
  }

  /**
   * @inheritDoc
   */
  public function getFormatsFromAttributes(array $attributes): array {
    // TODO: Implement getFormatsFromAttributes() method.
  }

  /**
   * @inheritDoc
   */
  public function getFormatsFromOperation(
    string $resourceClass,
    string $operationName,
    string $operationType
  ): array {
    // TODO: Implement getFormatsFromOperation() method.
  }


}
