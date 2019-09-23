<?php


namespace Drupal\api_platform\Component\Serializer\Mapping;


/**
 * Knows how to get the class discriminator mapping for classes and objects.
 */
interface ClassDiscriminatorResolverInterface
{
  public function getMappingForClass(string $class): ?ClassDiscriminatorMapping;

  /**
   * @param object|string $object
   */
  public function getMappingForMappedObject($object): ?ClassDiscriminatorMapping;

  /**
   * @param object|string $object
   */
  public function getTypeForMappedObject($object): ?string;
}
