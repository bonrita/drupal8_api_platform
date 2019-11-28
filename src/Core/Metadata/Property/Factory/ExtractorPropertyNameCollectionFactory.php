<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Property\Factory;


use Drupal\api_platform\Core\Exception\ResourceClassNotFoundException;
use Drupal\api_platform\Core\Metadata\Extractor\ExtractorInterface;
use Drupal\api_platform\Core\Metadata\Property\PropertyNameCollection;

/**
 * Class ExtractorPropertyNameCollectionFactory
 *
 * Creates a property name collection using an extractor.
 *
 * @package Drupal\api_platform\Core\Metadata\Property\Factory
 */
final class ExtractorPropertyNameCollectionFactory implements PropertyNameCollectionFactoryInterface {

  private $extractor;
  private $decorated;

  public function __construct(ExtractorInterface $extractor, PropertyNameCollectionFactoryInterface $decorated = null)
  {
    $this->extractor = $extractor;
    $this->decorated = $decorated;
  }

  /**
   * @inheritDoc
   */
  public function create(
    string $resourceClass,
    array $options = []
  ): PropertyNameCollection {
    $propertyNames = [];
    $propertyNameCollection = null;

    if ($this->decorated) {
      try {
        $propertyNameCollection = $this->decorated->create($resourceClass, $options);
      } catch (ResourceClassNotFoundException $resourceClassNotFoundException) {
        // Ignore not found exceptions from parent
      }

      foreach ($propertyNameCollection as $propertyName) {
        $propertyNames[$propertyName] = $propertyName;
      }
    }

    if (!class_exists($resourceClass)) {
      if (null !== $propertyNameCollection) {
        return $propertyNameCollection;
      }

      throw new ResourceClassNotFoundException(sprintf('The resource class "%s" does not exist.', $resourceClass));
    }

    if ($properties = $this->extractor->getResources()[$resourceClass]['properties'] ?? false) {
      foreach ($properties as $propertyName => $property) {
        $propertyNames[$propertyName] = $propertyName;
      }
    }

    return new PropertyNameCollection(array_values($propertyNames));
  }

}
