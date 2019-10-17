<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\PropertyInfo\Metadata\Property;

use Drupal\api_platform\Core\Exception\RuntimeException;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use Drupal\api_platform\Core\Metadata\Property\PropertyNameCollection;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

/**
 * PropertyInfo collection loader.
 *
 * This is not a decorator on purpose because it should always have the top priority.
 *
 */
final class PropertyInfoPropertyNameCollectionFactory implements PropertyNameCollectionFactoryInterface
{

  private $propertyInfo;

  public function __construct(PropertyInfoExtractorInterface $propertyInfo)
  {
    $this->propertyInfo = $propertyInfo;
  }

  /**
   * @inheritDoc
   */
  public function create(
    string $resourceClass,
    array $options = []
  ): PropertyNameCollection {
    $properties = $this->propertyInfo->getProperties($resourceClass, $options);
    if (null === $properties) {
      throw new RuntimeException(sprintf('There is no PropertyInfo extractor supporting the class "%s".', $resourceClass));
    }

    return new PropertyNameCollection($properties);
  }

}
