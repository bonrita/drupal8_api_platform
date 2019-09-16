<?php
declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Resource\Factory;

use Drupal\api_platform\Core\Metadata\Resource\ResourceNameCollection;

/**
 * Class ExtractorResourceNameCollectionFactory
 *
 * @package ApiPlatform\Core\Metadata\Resource\Factory
 *
 * Creates a resource name collection from {@see ApiResource} configuration files.
 */
final class ExtractorResourceNameCollectionFactory implements ResourceNameCollectionFactoryInterface {

  /**
   * Creates the resource name collection.
   */
  public function create(): ResourceNameCollection {
    $classes = [];

    return new ResourceNameCollection(array_keys($classes));
  }

}
