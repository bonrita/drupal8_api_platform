<?php
declare(strict_types=1);

namespace Drupal\api_platform\Core\Routing;


use Drupal\api_platform\Core\Api\OperationType;
use Drupal\api_platform\Core\Api\OperationTypeDeprecationHelper;
use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\Routing\RouterInterface;

final class RouteNameResolver implements RouteNameResolverInterface {

//  private $router;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface|\Drupal\Core\Routing\PreloadableRouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The state key value store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The cache backend used to skip the state loading.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  public function __construct(RouteProviderInterface $route_provider, StateInterface $state, CacheBackendInterface $cache)
  {
//    $this->router = $router;
    $this->routeProvider = $route_provider;
    $this->state = $state;
    $this->cache = $cache;
  }


  /**
   * Finds the route name for a resource.
   *
   * @param bool|string $operationType
   *
   * @throws InvalidArgumentException
   */
  public function getRouteName(string $resourceClass, $operationType): string {
    if (\func_num_args() > 2) {
      $context = func_get_arg(2);
    } else {
      $context = [];
    }

    $operationType = OperationTypeDeprecationHelper::getOperationType($operationType);

    // Ensure that the state query is cached to skip the database query, if
    // possible.
    $key = 'routing.non_admin_routes';

    if ($cache = $this->cache->get($key)) {
      $routes = $cache->data;
    }
    else {
      $routes = $this->state->get($key, []);
      $this->cache->set($key, $routes, Cache::PERMANENT, ['routes']);
    }

    // @todo Probably cache results from this loop e.g $cid = $routeName.$operation.$currentResourceClass.$operationType
    foreach ($routes as $routeName) {
      $route = $this->routeProvider->getRouteByName($routeName);
      $currentResourceClass = $route->getDefault('_api_resource_class');
      $operation = $route->getDefault(sprintf('_api_%s_operation_name', $operationType));
      $methods = $route->getMethods();

      if ($resourceClass === $currentResourceClass && null !== $operation && (empty($methods) || \in_array('GET', $methods, true))) {

        if (OperationType::SUBRESOURCE === $operationType && false === $this->isSameSubresource($context, $route->getDefault('_api_subresource_context'))) {
          continue;
        }

        return $routeName;
      }
    }

    throw new InvalidArgumentException(sprintf('No %s route associated with the type "%s".', $operationType, $resourceClass));
  }

}
