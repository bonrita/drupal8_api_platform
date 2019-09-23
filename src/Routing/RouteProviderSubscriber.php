<?php
declare(strict_types=1);

namespace Drupal\api_platform\Routing;

use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\RouteCollection;

final class RouteProviderSubscriber implements EventSubscriberInterface {

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


  public function __construct( array $formats, bool $entrypointEnabled = true) {
    $this->fileLoader = new XmlFileLoader(new FileLocator(__DIR__.'/../DependencyInjection/Resources/config/routing'));

    $this->formats = $formats;
    $this->entrypointEnabled = $entrypointEnabled;
  }


  /**
   * Provides routes on route rebuild time.
   *
   * @param \Drupal\Core\Routing\RouteBuildEvent $event
   *   The route build event.
   */
  public function onDynamicRouteEvent(RouteBuildEvent $event) {
    $route_collection = $event->getRouteCollection();

    $collection = new RouteCollection();

    $this->loadExternalFiles($collection);

    $route_collection->addCollection($collection);

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
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    $events[RoutingEvents::DYNAMIC][] = ['onDynamicRouteEvent'];
    return $events;
  }

}
