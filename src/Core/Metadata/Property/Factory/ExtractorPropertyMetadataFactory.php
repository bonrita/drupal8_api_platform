<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Property\Factory;


use Drupal\api_platform\Core\Metadata\Extractor\ExtractorInterface;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Drupal\api_platform\Core\Exception\PropertyNotFoundException;
use Drupal\api_platform\Core\Metadata\Property\PropertyMetadata;

final class ExtractorPropertyMetadataFactory implements PropertyMetadataFactoryInterface {

  private $extractor;
  private $decorated;

  public function __construct(ExtractorInterface $extractor, PropertyMetadataFactoryInterface $decorated = null)
  {
    $this->extractor = $extractor;
    $this->decorated = $decorated;
  }

  /**
   * @inheritDoc
   */
  public function create(string $resourceClass, string $property, array $options = []): PropertyMetadata {
    $parentPropertyMetadata = null;

    if ($this->decorated) {
      try {
        $parentPropertyMetadata = $this->decorated->create($resourceClass, $property, $options);
      } catch (PropertyNotFoundException $propertyNotFoundException) {
        // Ignore not found exception from decorated factories
      }
    }

    $isInterface = interface_exists($resourceClass);

    if (
      !property_exists($resourceClass, $property) && !$isInterface ||
      null === ($propertyMetadata = $this->extractor->getResources()[$resourceClass]['properties'][$property] ?? null)
    ) {
      return $this->handleNotFound($parentPropertyMetadata, $resourceClass, $property);
    }

  }

  /**
   * Returns the metadata from the decorated factory if available or throws an exception.
   *
   * @throws PropertyNotFoundException
   */
  private function handleNotFound(?PropertyMetadata $parentPropertyMetadata, string $resourceClass, string $property): PropertyMetadata
  {
    if ($parentPropertyMetadata) {
      return $parentPropertyMetadata;
    }

    throw new PropertyNotFoundException(sprintf('Property "%s" of the resource class "%s" not found.', $property, $resourceClass));
  }



}
