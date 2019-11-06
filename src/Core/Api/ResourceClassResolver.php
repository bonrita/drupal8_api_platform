<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Entity\Exception\NoCorrespondingEntityClassException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * Class ResourceClassResolver
 *
 * @package Drupal\api_platform\Core\Api
 */
class ResourceClassResolver implements ResourceClassResolverInterface {

  private $resourceNameCollectionFactory;
  private $localIsResourceClassCache = [];

  /**
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  private $entityTypeRepository;

  /**
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  private $entityTypeBundleInfo;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * @var \Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface
   */
  private $resourceMetadataFactory;

  public function __construct(
    ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory,
    EntityTypeRepositoryInterface $entityTypeRepository,
    EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    EntityTypeManagerInterface $entityTypeManager,
    ResourceMetadataFactoryInterface $resourceMetadataFactory
)
  {
    $this->resourceNameCollectionFactory = $resourceNameCollectionFactory;
    $this->entityTypeRepository = $entityTypeRepository;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
    $this->entityTypeManager = $entityTypeManager;
    $this->resourceMetadataFactory = $resourceMetadataFactory;
  }

  /**
   * @inheritDoc
   */
  public function getResourceClass(
    $value,
    string $resourceClass = NULL,
    bool $strict = FALSE
  ): string {
    if ($strict && null === $resourceClass) {
      throw new InvalidArgumentException('Strict checking is only possible when resource class is specified.');
    }

    $actualClass = \is_object($value) && !$value instanceof \Traversable ? $this->getObjectClass($value) : null;

    if (null === $actualClass && null === $resourceClass) {
      throw new InvalidArgumentException('Resource type could not be determined. Resource class must be specified.');
    }

    if (null !== $actualClass && !$this->isResourceClass($actualClass)) {
      throw new InvalidArgumentException(sprintf('No resource class found for object of type "%s".', $actualClass));
    }

    if (null !== $resourceClass && !$this->isResourceClass($resourceClass)) {
      throw new InvalidArgumentException(sprintf('Specified class "%s" is not a resource class.', $resourceClass));
    }

    if ($strict && null !== $actualClass && !is_a($actualClass, $resourceClass, true)) {
      throw new InvalidArgumentException(sprintf('Object of type "%s" does not match "%s" resource class.', $actualClass, $resourceClass));
    }

    $targetClass = $actualClass ?? $resourceClass;
    $mostSpecificResourceClass = null;

    foreach ($this->resourceNameCollectionFactory->create() as $resourceClassName) {
      if (!is_a($targetClass, $resourceClassName, true)) {
        continue;
      }

      if (null === $mostSpecificResourceClass || is_subclass_of($resourceClassName, $mostSpecificResourceClass)) {
        $mostSpecificResourceClass = $resourceClassName;
      }
    }

    if (null === $mostSpecificResourceClass) {
      throw new \LogicException('Unexpected execution flow.');
    }

    return $mostSpecificResourceClass;
  }

  /**
   * @inheritDoc
   */
  public function isResourceClass(string $type): bool {
    if (isset($this->localIsResourceClassCache[$type])) {
      return $this->localIsResourceClassCache[$type];
    }

    foreach ($this->resourceNameCollectionFactory->create() as $resourceClass) {
      if (is_a($type, $resourceClass, true)) {
        return $this->localIsResourceClassCache[$type] = true;
      }
    }

    return $this->localIsResourceClassCache[$type] = false;
  }

  /**
   * {@inheritDoc}
   */
  public function getBundles(string $resourceClass): array {
    $entityTypeId = $this->getEntityTypeId($resourceClass);
    return $this->entityTypeBundleInfo->getBundleInfo($entityTypeId);
  }

  /**
   * {@inheritDoc}
   */
  public function getIdKey(string $resourceClass): string {
    $entityTypeId = $this->getEntityTypeId($resourceClass);
    $definition = $this->entityTypeManager->getDefinition($entityTypeId);
   return $definition->getKey('id');
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityTypeId(string $resourceClass): string {

   try{
     $entityTypeId = $this->entityTypeRepository->getEntityTypeFromClass($resourceClass);
   } catch (NoCorrespondingEntityClassException $e) {
     $tt =  $this->resourceMetadataFactory->create($resourceClass);
     $entityTypeId = NULL;
   }
    return $entityTypeId;
  }

  /**
   * {@inheritDoc}
   */
  public function getBundleKey(string $resourceClass): string {
    $entityTypeId = $this->getEntityTypeId($resourceClass);
    $definition = $this->entityTypeManager->getDefinition($entityTypeId);
    return $definition->getKey('bundle');
  }

  public function getFieldMainProperty(string $fieldName, string $entityTypeId) {
    // Get the base field definitions for this entity type.
    $base_field_definitions = $this->getEntityFieldManager()->getBaseFieldDefinitions($entity_type_definition->id());
  }

  /**
   * Gets the entity type definition.
   *
   * @param string $entity_type_id
   *   The entity type ID to load the definition for.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface
   *   The loaded entity type definition.
   *
   * @throws \Symfony\Component\Serializer\Exception\UnexpectedValueException
   */
  protected function getEntityTypeDefinition($entity_type_id) {
    /** @var \Drupal\Core\Entity\EntityTypeInterface $entity_type_definition */
    // Get the entity type definition.
    $entity_type_definition = $this->entityTypeManager->getDefinition($entity_type_id, FALSE);

    // Don't try to create an entity without an entity type id.
    if (!$entity_type_definition) {
      throw new UnexpectedValueException(sprintf('The specified entity type "%s" does not exist. A valid entity type is required.', $entity_type_id));
    }

    return $entity_type_definition;
  }

}
