<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Routing;

use Drupal\api_platform\Core\Api\OperationType;
use Drupal\api_platform\Core\Api\OperationTypeDeprecationHelper;
use Drupal\api_platform\Core\Routing\RouteNameGenerator;
use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Drupal\api_platform\Core\Api\OperationAwareFormatsProviderInterface;
use Drupal\api_platform\Core\PathResolver\OperationPathResolverInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class RouterOperationPathResolver
 *
 * @package Drupal\api_platform\Core\Routing
 *
 * Resolves the operations path using a Symfony route.
 */
final class RouterOperationPathResolver implements OperationPathResolverInterface, OperationAwareFormatsProviderInterface {

  private $router;
  private $deferred;

  public function __construct(Router $router, OperationPathResolverInterface $deferred)
  {
    $this->router = $router;
    $this->deferred = $deferred;
  }

  /**
   * @inheritDoc
   */
  public function resolveOperationPath(
    string $resourceShortName,
    array $operation,
    $operationType
  ): string {
    if (\func_num_args() >= 4) {
      $operationName = (string) func_get_arg(3);
    } else {
      @trigger_error(sprintf('Method %s() will have a 4th `string $operationName` argument in version 3.0. Not defining it is deprecated since 2.1.', __METHOD__), E_USER_DEPRECATED);

      $operationName = null;
    }

    if (isset($operation['route_name'])) {
      $routeName = $operation['route_name'];
    } elseif (OperationType::SUBRESOURCE === $operationType) {
      throw new InvalidArgumentException('Subresource operations are not supported by the RouterOperationPathResolver without a route name.');
    } elseif (null === $operationName) {
      return $this->deferred->resolveOperationPath($resourceShortName, $operation, OperationTypeDeprecationHelper::getOperationType($operationType), $operationName);
    } else {
      $routeName = RouteNameGenerator::generate($operationName, $resourceShortName, $operationType);
    }

    if (!$route = $this->router->getRouteCollection()->get($routeName)) {
      throw new InvalidArgumentException(sprintf('The route "%s" of the resource "%s" was not found.', $routeName, $resourceShortName));
    }

    return $route->getPath();
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
