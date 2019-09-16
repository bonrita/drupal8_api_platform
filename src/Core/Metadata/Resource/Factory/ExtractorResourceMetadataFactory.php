<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Resource\Factory;


use Drupal\api_platform\Core\Exception\ResourceClassNotFoundException;
use Drupal\api_platform\Core\Metadata\Resource\ResourceMetadata;

final class ExtractorResourceMetadataFactory implements ResourceMetadataFactoryInterface {

  /**
   * Creates a resource metadata.
   *
   * @throws ResourceClassNotFoundException
   */
  public function create(string $resourceClass): ResourceMetadata {
    // TODO: Implement create() method.
  }

}
