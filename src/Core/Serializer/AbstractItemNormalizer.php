<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Serializer;


use Drupal\api_platform\Core\Api\IriConverterInterface;
use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\api_platform\Core\DataProvider\ItemDataProviderInterface;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Util\ClassInfoTrait;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

/**
 * Base item normalizer.
 */
abstract class AbstractItemNormalizer extends AbstractObjectNormalizer {

  use ClassInfoTrait;
  use InputOutputMetadataTrait;

  protected $propertyNameCollectionFactory;
  protected $propertyMetadataFactory;
  protected $iriConverter;
  protected $resourceClassResolver;
  protected $propertyAccessor;
  protected $itemDataProvider;
  protected $allowPlainIdentifiers;
  protected $dataTransformers = [];
  protected $localCache = [];


  public function __construct(PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory, PropertyMetadataFactoryInterface $propertyMetadataFactory, IriConverterInterface $iriConverter, ResourceClassResolverInterface $resourceClassResolver, PropertyAccessorInterface $propertyAccessor = null, NameConverterInterface $nameConverter = null, ClassMetadataFactoryInterface $classMetadataFactory = null, ItemDataProviderInterface $itemDataProvider = null, bool $allowPlainIdentifiers = false, array $defaultContext = [], iterable $dataTransformers = [], ResourceMetadataFactoryInterface $resourceMetadataFactory = null)
  {
    parent::__construct($classMetadataFactory, $nameConverter, null);

    $this->propertyNameCollectionFactory = $propertyNameCollectionFactory;
    $this->propertyMetadataFactory = $propertyMetadataFactory;
    $this->iriConverter = $iriConverter;
    $this->resourceClassResolver = $resourceClassResolver;
    $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    $this->itemDataProvider = $itemDataProvider;
    $this->allowPlainIdentifiers = $allowPlainIdentifiers;
    $this->dataTransformers = $dataTransformers;
    $this->resourceMetadataFactory = $resourceMetadataFactory;

  }

  /**
   * {@inheritdoc}
   *
   * Unused in this context.
   */
  protected function extractAttributes($object, $format = null, array $context = [])
  {
    return [];
  }

  /**
   * @inheritDoc
   */
  protected function getAttributeValue(
    $object,
    $attribute,
    $format = NULL,
    array $context = []
  ) {
    $context['api_attribute'] = $attribute;
  }

  /**
   * @inheritDoc
   */
  protected function setAttributeValue(
    $object,
    $attribute,
    $value,
    $format = NULL,
    array $context = []
  ) {
    $tt =0;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = null)
  {
    if (!\is_object($data) || $data instanceof \Traversable) {
      return false;
    }

    return $this->resourceClassResolver->isResourceClass($this->getObjectClass($data));
  }

  /**
   * Gets and caches attributes for the given object, format and context.
   *
   * @param object      $object
   * @param string|null $format
   * @param array       $context
   *
   * @return string[]
   */
  protected function getAttributes($object, $format = null, array $context)
  {
    $allowedAttributes = $this->getAllowedAttributes($object, $context, true);
    return $allowedAttributes;
  }

  /**
   * {@inheritdoc}
   */
  protected function getAllowedAttributes($classOrObject, array $context, $attributesAsString = false)
  {
    $options = $this->getFactoryOptions($context);
    $propertyNames = $this->propertyNameCollectionFactory->create($context['resource_class'], $options);

    $allowedAttributes = [];
    foreach ($propertyNames as $propertyName) {
      $propertyMetadata = $this->propertyMetadataFactory->create($context['resource_class'], $propertyName, $options);

      if (
        $this->isAllowedAttribute($classOrObject, $propertyName, null, $context) &&
        (
          isset($context['api_normalize']) && $propertyMetadata->isReadable() ||
          isset($context['api_denormalize']) && ($propertyMetadata->isWritable() || !\is_object($classOrObject) && $propertyMetadata->isInitializable())
        )
      ) {
        $allowedAttributes[] = $propertyName;
      }
    }

    return $allowedAttributes;
  }

  /**
   * Gets a valid context for property metadata factories.
   *
   * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/PropertyInfo/Extractor/SerializerExtractor.php
   */
  protected function getFactoryOptions(array $context): array
  {
    $options = [];

    if (isset($context[self::GROUPS])) {
      $options['serializer_groups'] = $context[self::GROUPS];
    }

    if (isset($context['collection_operation_name'])) {
      $options['collection_operation_name'] = $context['collection_operation_name'];
    }

    if (isset($context['item_operation_name'])) {
      $options['item_operation_name'] = $context['item_operation_name'];
    }

    if (isset($context['entity_class'])) {
      $options['entity_class'] = $context['entity_class'];
    }

    return $options;
  }

}
