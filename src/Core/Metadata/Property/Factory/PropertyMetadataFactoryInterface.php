<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Property\Factory;

use ApiPlatform\Core\Exception\PropertyNotFoundException;
use ApiPlatform\Core\Metadata\Property\PropertyMetadata;

/**
 * Interface PropertyMetadataFactoryInterface
 *
 * @package ApiPlatform\Core\Metadata\Property\Factory
 *
 * Creates a property metadata value object.
 */
interface PropertyMetadataFactoryInterface {

  /**
   * Creates a property metadata.
   *
   * @throws PropertyNotFoundException
   */
  public function create(string $resourceClass, string $property, array $options = []): PropertyMetadata;

}
