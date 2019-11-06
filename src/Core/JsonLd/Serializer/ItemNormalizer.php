<?php


namespace Drupal\api_platform\Core\JsonLd\Serializer;


use Drupal\api_platform\Core\Api\IriConverterInterface;
use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\api_platform\Core\JsonLd\ContextBuilderInterface;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Serializer\AbstractItemNormalizer;
use Drupal\api_platform\Core\Serializer\ContextTrait;
use Drupal\api_platform\Core\Util\ClassInfoTrait;
use Drupal\api_platform\PropertyInfo\Extractor\EntityExtractor;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Exception\NoCorrespondingEntityClassException;
use Drupal\field\FieldConfigInterface;
use Drupal\serialization\Normalizer\ContentEntityNormalizer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class ItemNormalizer extends AbstractItemNormalizer {

  use JsonLdContextTrait;
  use ClassInfoTrait;
  use ContextTrait;

  public const FORMAT = 'jsonld';

  /**
   * @var \Drupal\api_platform\Core\JsonLd\ContextBuilderInterface
   */
  private $contextBuilder;

  /**
   * @var \Drupal\serialization\Normalizer\ContentEntityNormalizer
   */
  private $decorated;

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * @var \Drupal\api_platform\PropertyInfo\Extractor\EntityExtractor
   */
  private $entityExtractor;

  public function __construct(
    ResourceMetadataFactoryInterface $resourceMetadataFactory,
    PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory,
    PropertyMetadataFactoryInterface $propertyMetadataFactory,
    IriConverterInterface $iriConverter,
    ResourceClassResolverInterface $resourceClassResolver,
    ContextBuilderInterface $contextBuilder,
    ContentEntityNormalizer $decorated,
    RequestStack $requestStack,
    EntityExtractor $entityExtractor,
    PropertyAccessorInterface $propertyAccessor = NULL,
    NameConverterInterface $nameConverter = NULL,
    ClassMetadataFactoryInterface $classMetadataFactory = NULL,
    array $defaultContext = [],
    iterable $dataTransformers = []
  ) {
    parent::__construct(
      $propertyNameCollectionFactory,
      $propertyMetadataFactory,
      $iriConverter,
      $resourceClassResolver,
      $propertyAccessor,
      $nameConverter,
      $classMetadataFactory,
      NULL,
      FALSE,
      $defaultContext,
      $dataTransformers,
      $resourceMetadataFactory
    );

    $this->contextBuilder = $contextBuilder;

    $this->decorated = $decorated;

    $this->requestStack = $requestStack;
    $this->entityExtractor = $entityExtractor;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL): bool {
    return $this->decorated->supportsNormalization($data, $format);
    return self::FORMAT === $format && parent::supportsNormalization(
        $data,
        $format
      );
  }

  public function denormalize(
    $data,
    $type,
    $format = NULL,
    array $context = []
  ) {

    $this->decorated->setSerializer($this->serializer);

//    $data = is_object($data)

    if (is_array($data) && isset($context['collection_operation_name'], $context['resource_class'])
      && 'post' === $context['collection_operation_name']) {
      $bundleKey = $this->resourceClassResolver->getBundleKey(
        $context['resource_class']
      );

      if ($this->requestStack->getCurrentRequest()->query->has(
        $bundleKey
      )) {
        $targetBundle = $this->requestStack->getCurrentRequest(
        )->query->get($bundleKey);
        $context['bundle'] = $targetBundle;
        $fieldStorageDefinition = $this->entityExtractor->getField($bundleKey, $context);
        $targetIdKey = $fieldStorageDefinition ? $fieldStorageDefinition->getFieldStorageDefinition()->getMainPropertyName() : 'value';
        $data[$bundleKey][0][$targetIdKey] = $targetBundle;

        $data = array_map(function ($item) {
          return is_array($item) ? $item : [$item];
        }, $data);

      }
    }

    try {
      $result = $this->decorated->denormalize($data, $type, $format, $context);
    } catch (NoCorrespondingEntityClassException $e) {
      return $data;
    }

    return $result;
  }


  /**
   * {@inheritdoc}
   *
   * @throws LogicException
   */
  public function normalize($object, $format = NULL, array $context = []) {

    if (NULL !== $this->getOutputClass(
        $this->getObjectClass($object),
        $context
      )) {
      return parent::normalize($object, $format, $context);
    }

    $resourceClass = $this->resourceClassResolver->getResourceClass(
      $object,
      $context['resource_class'] ?? NULL
    );

    $context = $this->initContext($resourceClass, $context);

    $iri = $this->iriConverter->getIriFromItem($object);
    $context['iri'] = $iri;
    $context['api_normalize'] = TRUE;

    $metadata = $this->addJsonLdContext(
      $this->contextBuilder,
      $resourceClass,
      $context
    );

    $this->decorated->setSerializer($this->serializer);
    $data = $this->decorated->normalize($object, $format, $context);
    if (!\is_array($data)) {
      return $data;
    }

    $data = $this->flattenValues($object, $data);

    $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);

    $metadata['@id'] = $iri;
    $metadata['@type'] = $resourceMetadata->getIri(
    ) ?: $resourceMetadata->getShortName();

    return $metadata + $data;
  }

  private function flattenValues(EntityInterface $object, array $data): array {
    $results = [];

    foreach ($data as $fieldName => $value) {
      $fieldDefinition = $object->getFieldDefinition($fieldName);
      if ($fieldDefinition instanceof FieldConfigInterface) {
        $fieldDefinition = $fieldDefinition->getFieldStorageDefinition();
      }

      if ($fieldDefinition->isMultiple()) {
        if (empty($value)) {
          $results[$fieldName] = [];
        }
        else {
          $results[$fieldName] = array_map(
            function ($item) use ($fieldDefinition) {
              return $item[$fieldDefinition->getMainPropertyName()];
            },
            $value
          );
        }

      }
      elseif (empty($value)) {
        $results[$fieldName] = '';
      }
      else {
        $results[$fieldName] = $value[0][$fieldDefinition->getMainPropertyName(
        )];
      }
    }

    return $results;
  }


}
