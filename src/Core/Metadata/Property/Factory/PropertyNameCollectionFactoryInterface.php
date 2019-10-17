<?php
declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Property\Factory;

use Drupal\api_platform\Core\Exception\ResourceClassNotFoundException;
use Drupal\api_platform\Core\Metadata\Property\PropertyNameCollection;

/**
 * Creates a property name collection value object.
 *
 */
interface PropertyNameCollectionFactoryInterface
{

  /**
   * Creates the property name collection for the given class and options.
   *
   * @throws ResourceClassNotFoundException
   */
  public function create(string $resourceClass, array $options = []): PropertyNameCollection;

}
