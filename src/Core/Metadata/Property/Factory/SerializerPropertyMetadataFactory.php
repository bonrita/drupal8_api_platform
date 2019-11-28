<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Property\Factory;

use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\api_platform\Core\Exception\PropertyNotFoundException;
use Drupal\api_platform\Core\Exception\ResourceClassNotFoundException;
use Drupal\api_platform\Core\Metadata\Property\PropertyMetadata;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Util\ResourceClassInfoTrait;
use Drupal\api_platform\PropertyInfo\Extractor\EntityExtractor;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface as SerializerClassMetadataFactoryInterface;

/**
 * Class SerializerPropertyMetadataFactory
 *
 * Populates read/write and link status using serialization groups.
 *N
 * @package Drupal\api_platform\Core\Metadata\Property\Factory
 */
final class SerializerPropertyMetadataFactory implements PropertyMetadataFactoryInterface {

  use ResourceClassInfoTrait;

  private $serializerClassMetadataFactory;
  private $decorated;

  /**
   * @var \Drupal\api_platform\PropertyInfo\Extractor\EntityExtractor
   */
  private $entityExtractor;

  public function __construct(ResourceMetadataFactoryInterface $resourceMetadataFactory, SerializerClassMetadataFactoryInterface $serializerClassMetadataFactory, PropertyMetadataFactoryInterface $decorated, ResourceClassResolverInterface $resourceClassResolver = null, EntityExtractor $entityExtractor = NULL)
  {
    $this->resourceMetadataFactory = $resourceMetadataFactory;
    $this->serializerClassMetadataFactory = $serializerClassMetadataFactory;
    $this->decorated = $decorated;
    $this->resourceClassResolver = $resourceClassResolver;
    $this->entityExtractor = $entityExtractor;
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

    // in case of a property inherited (in a child class), we need it's properties
    // to be mapped against serialization groups instead of the parent ones.
    if (null !== ($childResourceClass = $propertyMetadata->getChildInherited())) {
      $resourceClass = $childResourceClass;
    }

    try {
      [$normalizationGroups, $denormalizationGroups] = $this->getEffectiveSerializerGroups($options, $resourceClass);
    } catch (ResourceClassNotFoundException $e) {
      // TODO: for input/output classes, the serializer groups must be read from the actual resource class
      return $propertyMetadata;
    }

    $propertyMetadata = $this->transformReadWrite($propertyMetadata, $resourceClass, $property, $normalizationGroups, $denormalizationGroups, $options);

    return $this->transformLinkStatus($propertyMetadata, $normalizationGroups, $denormalizationGroups);
  }

  /**
   * Sets readable/writable based on matching normalization/denormalization groups.
   *
   * A false value is never reset as it could be unreadable/unwritable for other reasons.
   * If normalization/denormalization groups are not specified, the property is implicitly readable/writable.
   *
   * @param string[]|null $normalizationGroups
   * @param string[]|null $denormalizationGroups
   */
  private function transformReadWrite(
   PropertyMetadata $propertyMetadata, string $resourceClass, string $propertyName, array $normalizationGroups = null, array $denormalizationGroups = null, array $options = []): PropertyMetadata
  {
    $groups = $this->getPropertySerializerGroups($resourceClass, $propertyName, $options);
    if (false !== $propertyMetadata->isReadable()) {
      $propertyMetadata = $propertyMetadata->withReadable(null === $normalizationGroups || !empty(array_intersect($normalizationGroups, $groups)));
    }

    if (false !== $propertyMetadata->isWritable()) {
      $propertyMetadata = $propertyMetadata->withWritable(null === $denormalizationGroups || !empty(array_intersect($denormalizationGroups, $groups)));
    }

    return $propertyMetadata;
  }

  /**
   * Gets the serializer groups defined on a property.
   *
   * @return string[]
   */
  private function getPropertySerializerGroups(string $class, string $property, array $options = []): array
  {

    $serializerClassMetadata = $this->serializerClassMetadataFactory->getMetadataFor($class);

//    unset($options['serializer_groups']);
    $fields = $this->entityExtractor->getProperties($class, $options);

    foreach ($serializerClassMetadata->getAttributesMetadata() as $serializerAttributeMetadata) {
      if ($property === $serializerAttributeMetadata->getName() && in_array($serializerAttributeMetadata->getName(), $fields)) {
        return $serializerAttributeMetadata->getGroups();
      }
    }

    return [];
  }

  /**
   * Gets the effective serializer groups used in normalization/denormalization.
   *
   * Groups are extracted in the following order:
   *
   * - From the "serializer_groups" key of the $options array.
   * - From metadata of the given operation ("collection_operation_name" and "item_operation_name" keys).
   * - From metadata of the current resource.
   *
   * @throws \Drupal\api_platform\Core\Exception\ResourceClassNotFoundException
   *
   * @return (string[]|null)[]
   */
  private function getEffectiveSerializerGroups(array $options, string $resourceClass): array
  {
    if (isset($options['serializer_groups'])) {
      $groups = (array) $options['serializer_groups'];

      return [$groups, $groups];
    }

    $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);

    if (isset($options['collection_operation_name'])) {
      $normalizationContext = $resourceMetadata->getCollectionOperationAttribute($options['collection_operation_name'], 'normalization_context', null, true);
      $denormalizationContext = $resourceMetadata->getCollectionOperationAttribute($options['collection_operation_name'], 'denormalization_context', null, true);
    } elseif (isset($options['item_operation_name'])) {
      $normalizationContext = $resourceMetadata->getItemOperationAttribute($options['item_operation_name'], 'normalization_context', null, true);
      $denormalizationContext = $resourceMetadata->getItemOperationAttribute($options['item_operation_name'], 'denormalization_context', null, true);
    } elseif (isset($options['graphql_operation_name'])) {
      $normalizationContext = $resourceMetadata->getGraphqlAttribute($options['graphql_operation_name'], 'normalization_context', null, true);
      $denormalizationContext = $resourceMetadata->getGraphqlAttribute($options['graphql_operation_name'], 'denormalization_context', null, true);
    } else {
      $normalizationContext = $resourceMetadata->getAttribute('normalization_context');
      $denormalizationContext = $resourceMetadata->getAttribute('denormalization_context');
    }

    return [
      isset($normalizationContext['groups']) ? (array) $normalizationContext['groups'] : null,
      isset($denormalizationContext['groups']) ? (array) $denormalizationContext['groups'] : null,
    ];
  }

  /**
   * Sets readableLink/writableLink based on matching normalization/denormalization groups.
   *
   * If normalization/denormalization groups are not specified,
   * set link status to false since embedding of resource must be explicitly enabled
   *
   * @param string[]|null $normalizationGroups
   * @param string[]|null $denormalizationGroups
   */
  private function transformLinkStatus(PropertyMetadata $propertyMetadata, array $normalizationGroups = null, array $denormalizationGroups = null): PropertyMetadata
  {
    // No need to check link status if property is not readable and not writable
    if (false === $propertyMetadata->isReadable() && false === $propertyMetadata->isWritable()) {
      return $propertyMetadata;
    }

    $type = $propertyMetadata->getType();
    if (null === $type) {
      return $propertyMetadata;
    }

    $relatedClass = $type->isCollection() && ($collectionValueType = $type->getCollectionValueType()) ? $collectionValueType->getClassName() : $type->getClassName();

    // if property is not a resource relation, don't set link status (as it would have no meaning)
    if (null === $relatedClass || !$this->isResourceClass($relatedClass)) {
      return $propertyMetadata;
    }

    // find the resource class
    // this prevents serializer groups on non-resource child class from incorrectly influencing the decision
    if (null !== $this->resourceClassResolver) {
      $relatedClass = $this->resourceClassResolver->getResourceClass(null, $relatedClass);
    }

    $relatedGroups = $this->getClassSerializerGroups($relatedClass);

    if (null === $propertyMetadata->isReadableLink()) {
      $propertyMetadata = $propertyMetadata->withReadableLink(null !== $normalizationGroups && !empty(array_intersect($normalizationGroups, $relatedGroups)));
    }

    if (null === $propertyMetadata->isWritableLink()) {
      $propertyMetadata = $propertyMetadata->withWritableLink(null !== $denormalizationGroups && !empty(array_intersect($denormalizationGroups, $relatedGroups)));
    }

    return $propertyMetadata;
  }

}
