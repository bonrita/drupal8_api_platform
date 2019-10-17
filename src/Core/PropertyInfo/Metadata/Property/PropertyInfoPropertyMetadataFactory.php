<?php


namespace Drupal\api_platform\Core\PropertyInfo\Metadata\Property;


use Drupal\api_platform\Core\Exception\PropertyNotFoundException;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Property\PropertyMetadata;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

final class PropertyInfoPropertyMetadataFactory implements PropertyMetadataFactoryInterface {

  private $propertyInfo;

  private $decorated;

  public function __construct(
    PropertyInfoExtractorInterface $propertyInfo,
    PropertyMetadataFactoryInterface $decorated = NULL
  ) {
    $this->propertyInfo = $propertyInfo;
    $this->decorated = $decorated;
  }

  /**
   * @inheritDoc
   */
  public function create(
    string $resourceClass,
    string $name,
    array $options = []
  ): PropertyMetadata {
    if ($this->decorated) {
      try{
        $propertyMetadata = $this->decorated->create($resourceClass, $name, $options);
      } catch (PropertyNotFoundException $propertyNotFoundException) {
        $propertyMetadata = new PropertyMetadata();
      }
    }

    if (null === $propertyMetadata->getType()) {
      $types = $this->propertyInfo->getTypes($resourceClass, $name, $options);
      if (isset($types[0])) {
        $propertyMetadata = $propertyMetadata->withType($types[0]);
      }
    }

    if (null === $propertyMetadata->getDescription() && null !== $description = $this->propertyInfo->getShortDescription($resourceClass, $name, $options)) {
      $propertyMetadata = $propertyMetadata->withDescription($description);
    }

    if (null === $propertyMetadata->isReadable() && null !== $readable = $this->propertyInfo->isReadable($resourceClass, $name, $options)) {
      $propertyMetadata = $propertyMetadata->withReadable($readable);
    }

    if (null === $propertyMetadata->isWritable() && null !== $writable = $this->propertyInfo->isWritable($resourceClass, $name, $options)) {
      $propertyMetadata = $propertyMetadata->withWritable($writable);
    }


    if (method_exists($this->propertyInfo, 'isInitializable')) {
      if (null === $propertyMetadata->isInitializable() && null !== $initializable = $this->propertyInfo->isInitializable($resourceClass, $name, $options)) {
        $propertyMetadata = $propertyMetadata->withInitializable($initializable);
      }
    } else {
      // BC layer for Symfony < 4.2
      $ref = new \ReflectionClass($resourceClass);
      if ($ref->isInstantiable() && $constructor = $ref->getConstructor()) {
        foreach ($constructor->getParameters() as $constructorParameter) {
          if ($constructorParameter->name === $name && null === $propertyMetadata->isInitializable()) {
            $propertyMetadata = $propertyMetadata->withInitializable(true);
          }
        }
      }
    }

    return $propertyMetadata;

  }

}
