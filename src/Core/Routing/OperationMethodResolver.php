<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Routing;

use Drupal\api_platform\Core\Exception\RuntimeException;
use Drupal\api_platform\Core\Routing\OperationMethodResolverInterface;
use Symfony\Component\Routing\Route;

/**
 * Class OperationMethodResolver
 *
 * @package ApiPlatform\Core\Bridge\Symfony\Routing
 *
 * Resolves the HTTP method associated with an operation, extended for Symfony routing.
 */
final class OperationMethodResolver implements OperationMethodResolverInterface {

  /**
   * @inheritDoc
   */
  public function getCollectionOperationRoute(
    string $resourceClass,
    string $operationName
  ): Route {
    // TODO: Implement getCollectionOperationRoute() method.
  }

  /**
   * @inheritDoc
   */
  public function getItemOperationRoute(
    string $resourceClass,
    string $operationName
  ): Route {
    // TODO: Implement getItemOperationRoute() method.
  }

  /**
   * @inheritDoc
   */
  public function getCollectionOperationMethod(
    string $resourceClass,
    string $operationName
  ): string {
    // TODO: Implement getCollectionOperationMethod() method.
  }

  /**
   * @inheritDoc
   */
  public function getItemOperationMethod(
    string $resourceClass,
    string $operationName
  ): string {
    // TODO: Implement getItemOperationMethod() method.
  }

}
