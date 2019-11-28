<?php


namespace Drupal\api_platform\Core\JsonLd\Serializer;


use Drupal\api_platform\ApiEntity\ApiEntityInterface;
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
use Drupal\api_platform\Routing\RouteProviderSubscriber;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Exception\NoCorrespondingEntityClassException;
use Drupal\Core\Routing\RouteMatchInterface;
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

  /**
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  private $classResolver;

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

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
    iterable $dataTransformers = [],
    ClassResolverInterface $classResolver = NULL,
    RouteMatchInterface $routeMatch = NULL
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
    $this->classResolver = $classResolver;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL): bool {
    return self::FORMAT === $format && $this->decorated->supportsNormalization(
        $data,
        $format
      );
  }

  private function isUpdating(array $context): bool {
    return (isset($context['item_operation_name'], $context['resource_class'])
      && 'put' === $context['item_operation_name']);
  }

  private function isCreating(array $context): bool {
    return (isset($context['collection_operation_name'], $context['resource_class'])
      && 'post' === $context['collection_operation_name']);
  }

  public function denormalize(
    $data,
    $type,
    $format = NULL,
    array $context = []
  ) {

    $this->decorated->setSerializer($this->serializer);

    //    $data = is_object($data)

    $data = $this->prepareData($data, $context);

    try {
      if (is_a($type, ApiEntityInterface::class, TRUE)) {
        $actualEntityClass = $this->resourceClassResolver->getActualResourceClass(
          $type
        );
        $result = $this->decorated->denormalize(
          $data,
          $actualEntityClass,
          $format,
          $context
        );
      }
      else {
        $result = $this->decorated->denormalize(
          $data,
          $type,
          $format,
          $context
        );
      }
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

    // Add custom fields that were added on the resource class wrapper.
    $data = $this->addCustomFields($object, $context, $resourceClass, $data);

    // Remove fields not defined in groups.
    if (isset($context['groups'])) {
      $context['entity_class'] = TRUE;
      $attributes = $this->getAttributes($object, $format, $context);

      array_walk(
        $data,
        function (&$value, $key, $attrs) {
          if (!in_array($key, $attrs)) {
            $value = NULL;
          }
        },
        $attributes
      );
      $data = array_filter($data);
    }


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

  /**
   * @param $data
   * @param array $context
   *
   * @return mixed
   */
  private function prepareData($data, array $context) {
    if (!(is_array($data) && ($this->isUpdating($context) || $this->isCreating(
          $context
        )))) {
      return $data;
    }

    $data = $this->addEntityBundleKey($data, $context);
    $data = array_map(
      function ($item) {
        return is_array($item) ? $item : [$item];
      },
      $data
    );

    if ($this->isUpdating($context)) {
      $idKey = $this->resourceClassResolver->getIdKey(
        $context['resource_class']
      );
      if ($this->requestStack->getCurrentRequest()->attributes->has($idKey)) {
        $id = $this->requestStack->getCurrentRequest()->attributes->getInt(
          $idKey
        );
        if ($id > 0) {
          $data[$idKey] = [$id];
        }
      }
    }

    return $data;
  }

  /**
   * @param $object
   * @param array $context
   * @param string $resourceClass
   * @param $data
   *
   * @return array
   * @throws \Drupal\api_platform\Core\Exception\PropertyNotFoundException
   * @throws \ReflectionException
   */
  private function addCustomFields(
    $object,
    array $context,
    string $resourceClass,
    $data
  ): array {
    $resourceClassInstance = $this->classResolver->getInstanceFromDefinition(
      $resourceClass
    );
    $options = [
      'entity_class' => $object instanceof EntityInterface,
      'bundle' => $object instanceof EntityInterface ? $object->bundle() : '',
    ];
    $context['object'] = $object;
    $context['data'] = $data;
    $reflectionClass = new \ReflectionClass($resourceClass);

    foreach ($reflectionClass->getProperties() as $property) {
      $propertyMetadata = $this->propertyMetadataFactory->create(
        $resourceClass,
        $property->getName(),
        $options
      );
      $methodName = 'get' . ucfirst($property->getName());
      if ($propertyMetadata->isReadable() && $reflectionClass->hasMethod(
          $methodName
        )) {
        $method = new \ReflectionMethod($resourceClass, $methodName);
        if ($method && $method->isPublic()) {
          $data[$property->getName()] = $resourceClassInstance->{$methodName}(
            $context
          );
        }
      }
    }
    return $data;
  }

  /**
   * @param array $context
   *
   * @return string|null
   */
  private function getBundleName(array $context): ?string {
    /** @var \Symfony\Component\Routing\Route $route */
    $route = $this->routeMatch->getRouteObject();

    if ($route->hasDefault(RouteProviderSubscriber::DEFAULT_ROUTE_BUNDLE_KEY)) {
      $targetBundle = $route->getDefault(
        RouteProviderSubscriber::DEFAULT_ROUTE_BUNDLE_KEY
      );
    }
    elseif (isset($context['object_to_populate']) && $context['object_to_populate'] instanceof EntityInterface) {
      $targetBundle = $context['object_to_populate']->bundle();
    }
    return $targetBundle ?? NULL;
  }

  /**
   * @param $data
   * @param array $context
   *
   * @return array
   */
  private function addEntityBundleKey($data, array $context): array {
    $bundleKey = $this->resourceClassResolver->getBundleKey(
      $context['resource_class']
    );
    $targetBundle = $this->getBundleName($context);
    $context['bundle'] = $targetBundle;

    $fieldStorageDefinition = $this->entityExtractor->getField(
      $bundleKey,
      $context
    );
    $targetIdKey = $fieldStorageDefinition ? $fieldStorageDefinition->getFieldStorageDefinition(
    )->getMainPropertyName() : 'value';
    $data[$bundleKey][0][$targetIdKey] = $targetBundle;

    return $data;
  }

}
