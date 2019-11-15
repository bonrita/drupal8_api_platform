<?php

declare(strict_types=1);

namespace Drupal\api_platform\PropertyInfo\Extractor;

use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldStorageConfigInterface;
use Symfony\Component\PropertyInfo\PropertyAccessExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyListExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

/**
 * Class EntityExtractor
 *
 * Lists available Drupal entity properties using Drupal APIs.
 *
 * @package Drupal\api_platform\PropertyInfo\Extractor
 */
class EntityExtractor implements PropertyListExtractorInterface, PropertyTypeExtractorInterface, PropertyAccessExtractorInterface {

  /**
   * @var EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  private $entityTypeRepository;

  /**
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  private $entityFieldManager;

  /**
   * @var \Drupal\Core\Field\FieldTypePluginManagerInterface
   */
  private $fieldTypePluginManager;

  /**
   * @var \Drupal\api_platform\Core\Api\ResourceClassResolverInterface
   */
  private $resourceClassResolver;

  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    EntityTypeRepositoryInterface $entityTypeRepository,
    EntityFieldManagerInterface $entityFieldManager,
    FieldTypePluginManagerInterface $fieldTypePluginManager,
    ResourceClassResolverInterface $resourceClassResolver
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeRepository = $entityTypeRepository;
    $this->entityFieldManager = $entityFieldManager;
    $this->fieldTypePluginManager = $fieldTypePluginManager;
    $this->resourceClassResolver = $resourceClassResolver;
  }

  /**
   * @inheritDoc
   */
  public function getProperties($class, array $context = []) {
    if (!isset($context['entity_class']) || !\is_bool(
        $context['entity_class']
      )) {
      return NULL;
    }

    return array_keys($this->getFields($class, $context));
  }

  /**
   * @inheritDoc
   */
  public function getTypes($class, $property, array $context = []) {
    if (!isset($context['entity_class']) || !\is_bool(
        $context['entity_class']
      )) {
      return NULL;
    }

    $fieldStorageDefinition = $this->getFields($class, $context)[$property];
    $builtInType = $this->getBuiltInType($fieldStorageDefinition);

    if ($fieldStorageDefinition->isMultiple()) {
      $type = [
        new Type(
          Type::BUILTIN_TYPE_ARRAY,
          FALSE,
          NULL,
          TRUE,
          new Type(Type::BUILTIN_TYPE_INT),
          new Type($builtInType)
        ),
      ];
    }
    else {
      $type = [new Type($builtInType)];
    }

    return $type;
  }

  /**
   * @inheritDoc
   */
  public function isReadable($class, $property, array $context = []) {
    if (!isset($context['entity_class']) || !\is_bool(
        $context['entity_class']
      )) {
      return FALSE;
    }

    $context['resource_class'] = $class;
    $field =  $this->getField($property, $context);

    if ($field->isBaseField() && !$field->isInternal()) {
     return TRUE;
    }

    if (!$field->isBaseField()) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function isWritable($class, $property, array $context = []) {
    if($this->resourceClassResolver->getIdKey($class) === $property) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Get symfony's built in type.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $fieldStorageDefinition
   *
   * @return string
   */
  private function getBuiltInType(
    FieldStorageDefinitionInterface $fieldStorageDefinition
  ): string {
    $dataDefinition = $fieldStorageDefinition->getPropertyDefinition(
      $fieldStorageDefinition->getMainPropertyName()
    );
    switch ($dataDefinition->getDataType()) {
      case 'timestamp':
      case 'integer':
        $type = Type::BUILTIN_TYPE_INT;
        break;
      case 'string':
        $type = Type::BUILTIN_TYPE_STRING;
        break;
      case 'boolean':
        $type = Type::BUILTIN_TYPE_BOOL;
        break;
      default:
        $type = NULL;
        break;
    }
    return $type;
  }

  /**
   * Get field definition.
   *
   * @param string $fieldName
   * @param string $resourceClass
   * @param array $context
   *
   * @return \Drupal\Core\Field\FieldStorageDefinitionInterface
   */
  public function getField(
    string $fieldName,
    array $context
  ): FieldStorageDefinitionInterface {
    return $this->getFields($context['resource_class'], $context)[$fieldName];
  }

  private function getFields(string $resourceClass, array $context): array {
    $entityTypeId = $this->resourceClassResolver->getEntityTypeId($resourceClass);

    /** @var EntityTypeInterface $entityType */
    $entityType = $this->entityTypeManager->getDefinition($entityTypeId);
    if (!$entityType->entityClassImplements(FieldableEntityInterface::class)) {
      return [];
    }

    $entityFields = $this->entityFieldManager->getFieldStorageDefinitions(
      $entityType->id()
    );

    if (isset($context['bundle']) && !empty($context['bundle'])) {
      $bundle_fields = $this->entityFieldManager->getFieldDefinitions(
        $entityType->id(),
        $context['bundle']
      );
      $fields = array_filter(
        array_map(
          function ($item) use ($entityFields) {
            return $item instanceof FieldStorageConfigInterface ? $item : $entityFields[$item->getName(
            )];
          },
          $bundle_fields
        )
      );
    }
    else {
      $fields = $entityFields;
    }
    return $fields;
  }

}
