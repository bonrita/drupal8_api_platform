<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Property\Factory;


use Doctrine\Common\Annotations\Reader;
use Drupal\api_platform\Core\Exception\PropertyNotFoundException;
use Drupal\api_platform\Core\Metadata\Property\PropertyMetadata;

/**
 * Class AnnotationPropertyMetadataFactory
 *
 * Creates a property metadata from {@see ApiProperty} annotations.
 *
 * @package Drupal\api_platform\Core\Metadata\Property\Factory
 */
final class AnnotationPropertyMetadataFactory implements PropertyMetadataFactoryInterface {

  private $reader;
  private $decorated;

  public function __construct(Reader $reader, PropertyMetadataFactoryInterface $decorated = null)
  {
    $this->reader = $reader;
    $this->decorated = $decorated;
  }

  /**
   * @inheritDoc
   */
  public function create(
    string $resourceClass,
    string $property,
    array $options = []
  ): PropertyMetadata {
    $parentPropertyMetadata = null;
    if ($this->decorated) {
      try {
        $parentPropertyMetadata = $this->decorated->create($resourceClass, $property, $options);
      } catch (PropertyNotFoundException $propertyNotFoundException) {
        // Ignore not found exception from decorated factories
      }
    }

    return $this->handleNotFound($parentPropertyMetadata, $resourceClass, $property);
  }

  /**
   * Returns the metadata from the decorated factory if available or throws an exception.
   *
   * @throws PropertyNotFoundException
   */
  private function handleNotFound(?PropertyMetadata $parentPropertyMetadata, string $resourceClass, string $property): PropertyMetadata
  {
    if (null !== $parentPropertyMetadata) {
      return $parentPropertyMetadata;
    }

    throw new PropertyNotFoundException(sprintf('Property "%s" of class "%s" not found.', $property, $resourceClass));
  }

}
