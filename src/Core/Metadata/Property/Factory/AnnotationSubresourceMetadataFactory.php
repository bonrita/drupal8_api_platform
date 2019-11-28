<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Property\Factory;


use Doctrine\Common\Annotations\Reader;
use Drupal\api_platform\Core\Exception\PropertyNotFoundException;
use Drupal\api_platform\Core\Metadata\Property\PropertyMetadata;

/**
 * Class AnnotationSubresourceMetadataFactory
 *
 * Adds subresources to the properties metadata from {@see ApiResource} annotations.
 *
 * @package Drupal\api_platform\Core\Metadata\Property\Factory
 */
final class AnnotationSubresourceMetadataFactory implements PropertyMetadataFactoryInterface {

  private $reader;
  private $decorated;

  public function __construct(Reader $reader, PropertyMetadataFactoryInterface $decorated)
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
    $propertyMetadata = $this->decorated->create($resourceClass, $property, $options);

    return $propertyMetadata;
  }

}
