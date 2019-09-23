<?php


namespace Drupal\api_platform\Component\Serializer\Mapping;


use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;

class ClassDiscriminatorFromClassMetadata  implements ClassDiscriminatorResolverInterface
{
  /**
   * @var ClassMetadataFactoryInterface
   */
  private $classMetadataFactory;
  private $mappingForMappedObjectCache = [];

  public function __construct(ClassMetadataFactoryInterface $classMetadataFactory)
  {
    $this->classMetadataFactory = $classMetadataFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingForClass(string $class): ?ClassDiscriminatorMapping
  {
    if ($this->classMetadataFactory->hasMetadataFor($class)) {
      return $this->classMetadataFactory->getMetadataFor($class)->getClassDiscriminatorMapping();
    }

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingForMappedObject($object): ?ClassDiscriminatorMapping
  {
    if ($this->classMetadataFactory->hasMetadataFor($object)) {
      $metadata = $this->classMetadataFactory->getMetadataFor($object);

      if (null !== $metadata->getClassDiscriminatorMapping()) {
        return $metadata->getClassDiscriminatorMapping();
      }
    }

    $cacheKey = \is_object($object) ? \get_class($object) : $object;
    if (!\array_key_exists($cacheKey, $this->mappingForMappedObjectCache)) {
      $this->mappingForMappedObjectCache[$cacheKey] = $this->resolveMappingForMappedObject($object);
    }

    return $this->mappingForMappedObjectCache[$cacheKey];
  }

  /**
   * {@inheritdoc}
   */
  public function getTypeForMappedObject($object): ?string
  {
    if (null === $mapping = $this->getMappingForMappedObject($object)) {
      return null;
    }

    return $mapping->getMappedObjectType($object);
  }

  private function resolveMappingForMappedObject($object)
  {
    $reflectionClass = new \ReflectionClass($object);
    if ($parentClass = $reflectionClass->getParentClass()) {
      return $this->getMappingForMappedObject($parentClass->getName());
    }

    foreach ($reflectionClass->getInterfaceNames() as $interfaceName) {
      if (null !== ($interfaceMapping = $this->getMappingForMappedObject($interfaceName))) {
        return $interfaceMapping;
      }
    }

    return null;
  }
}
