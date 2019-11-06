<?php
declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Resource\Factory;

use Drupal\api_platform\Core\Metadata\Extractor\ExtractorInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\ResourceNameCollection;

/**
 * Class ExtractorResourceNameCollectionFactory
 *
 * @package Drupal\api_platform\Core\Metadata\Resource\Factory
 *
 * Creates a resource name collection from {@see ApiResource} configuration files.
 */
final class ExtractorResourceNameCollectionFactory implements ResourceNameCollectionFactoryInterface {

  private $extractor;
  private $decorated;

  public function __construct(ExtractorInterface $extractor, ResourceNameCollectionFactoryInterface $decorated = null)
  {
    $this->extractor = $extractor;
    $this->decorated = $decorated;
  }


  /**
   * Creates the resource name collection.
   */
  public function create(): ResourceNameCollection {
    $classes = [];

    if ($this->decorated) {
      foreach ($this->decorated->create() as $resourceClass) {
        $classes[$resourceClass] = true;
      }
    }

    foreach ($this->extractor->getResources() as $resourceClass => $resource) {
      $classes[$resourceClass] = true;
    }

    return new ResourceNameCollection(array_keys($classes));
  }

}
