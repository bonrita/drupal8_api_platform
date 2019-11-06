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

}
