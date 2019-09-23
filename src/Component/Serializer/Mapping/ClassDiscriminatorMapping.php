<?php


namespace Drupal\api_platform\Component\Serializer\Mapping;


class ClassDiscriminatorMapping {
  private $typeProperty;
  private $typesMapping;

  public function __construct(string $typeProperty, array $typesMapping = [])
  {
    $this->typeProperty = $typeProperty;
    $this->typesMapping = $typesMapping;
  }

  public function getTypeProperty(): string
  {
    return $this->typeProperty;
  }

  public function getClassForType(string $type): ?string
  {
    return $this->typesMapping[$type] ?? null;
  }

  /**
   * @param object|string $object
   */
  public function getMappedObjectType($object): ?string
  {
    foreach ($this->typesMapping as $type => $typeClass) {
      if (is_a($object, $typeClass)) {
        return $type;
      }
    }

    return null;
  }

  public function getTypesMapping(): array
  {
    return $this->typesMapping;
  }
}
