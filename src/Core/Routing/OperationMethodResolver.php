<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Routing;

use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Api\OperationType;
use Drupal\api_platform\Core\Exception\RuntimeException;
use Drupal\api_platform\Core\Routing\OperationMethodResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class OperationMethodResolver
 *
 * @package Drupal\api_platform\Core\Bridge\Symfony\Routing
 *
 * Resolves the HTTP method associated with an operation, extended for Symfony routing.
 */
final class OperationMethodResolver implements OperationMethodResolverInterface {

  private $router;
  private $resourceMetadataFactory;

  public function __construct(RouterInterface $router, ResourceMetadataFactoryInterface $resourceMetadataFactory)
  {
    $this->router = $router;
    $this->resourceMetadataFactory = $resourceMetadataFactory;
  }


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
   * Gets the route related to the given operation.
   *
   * @throws \ApiPlatform\Core\Exception\RuntimeException
   */
  private function getOperationRoute(string $resourceClass, string $operationName, string $operationType): Route
  {
    $routeName = $this->getRouteName($this->resourceMetadataFactory->create($resourceClass), $operationName, $operationType);
    if (null !== $routeName) {
      return $this->getRoute($routeName);
    }

    $operationNameKey = sprintf('_api_%s_operation_name', $operationType);

    foreach ($this->router->getRouteCollection()->all() as $routeName => $route) {
      $currentResourceClass = $route->getDefault('_api_resource_class');
      $currentOperationName = $route->getDefault($operationNameKey);

      if ($resourceClass === $currentResourceClass && $operationName === $currentOperationName) {
        return $route;
      }
    }

    throw new RuntimeException(sprintf('No route found for operation "%s" for type "%s".', $operationName, $resourceClass));
  }

  /**
   * @inheritDoc
   */
  public function getItemOperationMethod(
    string $resourceClass,
    string $operationName
  ): string {
    return $this->getOperationMethod($resourceClass, $operationName, OperationType::ITEM);
  }

  /**
   * {@inheritdoc}
   */
  public function getCollectionOperationMethod(string $resourceClass, string $operationName): string
  {
    return $this->getOperationMethod($resourceClass, $operationName, OperationType::COLLECTION);
  }

  /**
   * @throws \Drupal\api_platform\Core\Exception\RuntimeException
   */
  private function getOperationMethod(string $resourceClass, string $operationName, string $operationType): string
  {
    $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);

    if (OperationType::ITEM === $operationType) {
      $method = $resourceMetadata->getItemOperationAttribute($operationName, 'method');
    } else {
      $method = $resourceMetadata->getCollectionOperationAttribute($operationName, 'method');
    }

    if (null !== $method) {
      return strtoupper($method);
    }

    if (null === $routeName = $this->getRouteName($resourceMetadata, $operationName, $operationType)) {
      throw new RuntimeException(sprintf('Either a "route_name" or a "method" operation attribute must exist for the operation "%s" of the resource "%s".', $operationName, $resourceClass));
    }

    return $this->getRoute($routeName)->getMethods()[0] ?? 'GET';
  }


}
