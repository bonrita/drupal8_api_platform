<?php
declare(strict_types=1);

namespace Drupal\api_platform\Core\Routing;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\RouteCollection;

final class ApiLoader {

  /**
   * @var \Symfony\Component\Routing\Loader\XmlFileLoader
   */
  private $fileLoader;

  public function __construct() {
    $this->fileLoader = new XmlFileLoader(new FileLocator(__DIR__.'/../../DependencyInjection/Resources/config/routing'));
  }

  public function routes(): RouteCollection {

    $routeCollection = new RouteCollection();

    $this->loadExternalFiles($routeCollection);

    return $routeCollection;

  }

  /**
   * Load external files.
   */
  private function loadExternalFiles(RouteCollection $routeCollection): void {
    $routeCollection->addCollection($this->fileLoader->load('jsonld.xml'));
  }

}
