<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Property\Factory;


use ApiPlatform\Core\Exception\PropertyNotFoundException;
use ApiPlatform\Core\Metadata\Property\PropertyMetadata;

final class ExtractorPropertyMetadataFactory implements PropertyMetadataFactoryInterface {

  /**
   * @inheritDoc
   */
  public function create(string $resourceClass, string $property, array $options = []): PropertyMetadata {
    // TODO: Implement create() method.
  }


}
