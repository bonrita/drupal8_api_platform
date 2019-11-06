<?php
declare(strict_types=1);

namespace Drupal\api_platform\Routing;

use Drupal\api_platform\Core\PathResolver\OperationPathResolverInterface;
use Drupal\api_platform\Core\Routing\RouteNameGenerator;
use Drupal\api_platform\Core\Exception\RuntimeException;
use Drupal\api_platform\Core\Metadata\Resource\ResourceMetadata;
use Drupal\api_platform\Core\Api\OperationType;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


final class RouteProviderSubscriber implements EventSubscriberInterface {

  /**
   * @deprecated since version 2.1, to be removed in 3.0. Use {@see RouteNameGenerator::ROUTE_NAME_PREFIX} instead.
   */
  public const ROUTE_NAME_PREFIX = 'api_';
  public const DEFAULT_ACTION_PATTERN = 'api_platform.action.';

  /**
   * @var \Symfony\Component\Routing\Loader\XmlFileLoader
   */
  private $fileLoader;

  /**
   * @var array
   */
  private $formats;

  /**
   * @var bool
   */
  private $entrypointEnabled;

  private $resourceClassDirectories;

  /**
   * @var \Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface
   */
  private $resourceNameCollectionFactory;

  /**
   * @var \Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface
   */
  private $resourceMetadataFactory;

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  private $container;

  /**
   * @var \Drupal\api_platform\Core\PathResolver\OperationPathResolverInterface
   */
  private $operationPathResolver;


  public function __construct( ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory, ResourceMetadataFactoryInterface $resourceMetadataFactory, OperationPathResolverInterface $operationPathResolver, ContainerInterface $container, array $formats, array $resourceClassDirectories = [],  bool $entrypointEnabled = true) {
    $this->fileLoader = new XmlFileLoader(new FileLocator(__DIR__.'/../DependencyInjection/Resources/config/routing'));

    $this->formats = $formats;
    $this->entrypointEnabled = $entrypointEnabled;
    $this->resourceClassDirectories = $resourceClassDirectories;
    $this->resourceNameCollectionFactory = $resourceNameCollectionFactory;
    $this->resourceMetadataFactory = $resourceMetadataFactory;
    $this->container = $container;
    $this->operationPathResolver = $operationPathResolver;
  }

  /**
   * Provides routes on route rebuild time.
   *
   * @param \Drupal\Core\Routing\RouteBuildEvent $event
   *   The route build event.
   */
  public function onDynamicRouteEvent(RouteBuildEvent $event) {
    $routeCollection = $event->getRouteCollection();

    foreach ($this->resourceClassDirectories as $directory) {
      $routeCollection->addResource(new DirectoryResource($directory, '/\.php$/'));
    }

    $collection = new RouteCollection();

    $this->loadExternalFiles($collection);

    foreach ($this->resourceNameCollectionFactory->create() as $resourceClass) {
      $resourceMetadata = $this->resourceMetadataFactory->create(
        $resourceClass
      );

      $resourceShortName = $resourceMetadata->getShortName();

      if (null === $resourceShortName) {
        throw new InvalidResourceException(sprintf('Resource %s has no short name defined.', $resourceClass));
      }

      // Add routes.
      if (null !== $collectionOperations = $resourceMetadata->getCollectionOperations()) {
        foreach ($collectionOperations as $operationName => $operation) {
          $this->addRoute($routeCollection, $resourceClass, $operationName, $operation, $resourceMetadata, OperationType::COLLECTION);
        }
      }

      if (null !== $itemOperations = $resourceMetadata->getItemOperations()) {
        foreach ($itemOperations as $operationName => $operation) {
          $this->addRoute($routeCollection, $resourceClass, $operationName, $operation, $resourceMetadata, OperationType::ITEM);
        }
      }

      $gg =0;
    }

    $routeCollection->addCollection($collection);

  }

  /**
   * Load external files.
   */
  private function loadExternalFiles(RouteCollection $routeCollection): void {

    if ($this->entrypointEnabled) {
      $routeCollection->addCollection($this->fileLoader->load('api.xml'));
    }

    if (isset($this->formats['jsonld'])) {
      $routeCollection->addCollection($this->fileLoader->load('jsonld.xml'));
    }

  }

  /**
   * Creates and adds a route for the given operation to the route collection.
   *
   * @throws RuntimeException
   */
  private function addRoute(RouteCollection $routeCollection, string $resourceClass, string $operationName, array $operation, ResourceMetadata $resourceMetadata, string $operationType): void
  {
    $resourceShortName = $resourceMetadata->getShortName();

    if (isset($operation['route_name'])) {
      return;
    }

    if (!isset($operation['method'])) {
      throw new RuntimeException(sprintf('Either a "route_name" or a "method" operation attribute must exist for the operation "%s" of the resource "%s".', $operationName, $resourceClass));
    }

    if (null === $controller = $operation['controller'] ?? null) {
      $controller = sprintf('%s%s_%s', self::DEFAULT_ACTION_PATTERN, strtolower($operation['method']), $operationType);

      if (!$this->container->has($controller)) {
        throw new RuntimeException(sprintf('There is no builtin action for the %s %s operation. You need to define the controller yourself.', $operationType, $operation['method']));
      }
    }

    $path = trim(trim($resourceMetadata->getAttribute('route_prefix', 'api')), '/');
    $path .= $this->operationPathResolver->resolveOperationPath($resourceShortName, $operation, $operationType, $resourceClass, $operationName);

    $route = new Route(
      $path,
      [
        '_controller' => $controller,
        '_format' => null,
        '_api_resource_class' => $resourceClass,
        sprintf('_api_%s_operation_name', $operationType) => $operationName,
      ] + ($operation['defaults'] ?? []),
      $operation['requirements'] ?? ['_access' => 'TRUE'],
      $operation['options'] ?? [],
      $operation['host'] ?? '',
      $operation['schemes'] ?? [],
      [$operation['method']],
      $operation['condition'] ?? ''
    );

    $routeCollection->add(RouteNameGenerator::generate($operationName, $resourceShortName, $operationType), $route);
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    $events[RoutingEvents::DYNAMIC][] = ['onDynamicRouteEvent'];
    return $events;
  }

}
