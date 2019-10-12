<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Swagger\Serializer;

use Drupal\api_platform\Core\Metadata\Resource\ResourceMetadata;
use Drupal\api_platform\Core\Api\OperationType;
use Drupal\api_platform\Core\Api\FilterCollection;
use Drupal\api_platform\Core\Api\FilterLocatorTrait;
use Drupal\api_platform\Core\Api\OperationAwareFormatsProviderInterface;
use Drupal\api_platform\Core\Api\OperationMethodResolverInterface;
use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\api_platform\Core\Api\UrlGeneratorInterface;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Operation\Factory\SubresourceOperationFactoryInterface;
use Drupal\api_platform\Core\PathResolver\OperationPathResolverInterface;
use Drupal\api_platform\Core\Swagger\Serializer\ApiGatewayNormalizer;
use Drupal\api_platform\Core\Documentation\Documentation;
use Psr\Container\ContainerInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class DocumentationNormalizer
 *
 * @package Drupal\api_platform\Core\Swagger\Serializer
 *
 * Generates an OpenAPI specification (formerly known as Swagger). OpenAPI v2 and v3 are supported.
 */
final class DocumentationNormalizer implements NormalizerInterface {

  use FilterLocatorTrait;

  public const FORMAT = 'json';
  public const BASE_URL = 'base_url';
  public const SPEC_VERSION = 'spec_version';
  public const OPENAPI_VERSION = '3.0.2';
  public const SWAGGER_DEFINITION_NAME = 'swagger_definition_name';
  public const SWAGGER_VERSION = '2.0';

  /**
   * @deprecated
   */
  public const ATTRIBUTE_NAME = 'swagger_context';

  private $resourceMetadataFactory;
  private $propertyNameCollectionFactory;
  private $propertyMetadataFactory;
  private $resourceClassResolver;
  private $operationMethodResolver;
  private $operationPathResolver;
  private $nameConverter;
  private $oauthEnabled;
  private $oauthType;
  private $oauthFlow;
  private $oauthTokenUrl;
  private $oauthAuthorizationUrl;
  private $oauthScopes;
  private $apiKeys;
  private $subresourceOperationFactory;
  private $paginationEnabled;
  private $paginationPageParameterName;
  private $clientItemsPerPage;
  private $itemsPerPageParameterName;
  private $paginationClientEnabled;
  private $paginationClientEnabledParameterName;
  private $formatsProvider;
  private $defaultContext = [
    self::BASE_URL => '/',
    self::SPEC_VERSION => 2,
    ApiGatewayNormalizer::API_GATEWAY => false,
  ];

  /**
   * @param ContainerInterface|FilterCollection|null $filterLocator The new filter locator or the deprecated filter collection
   */
  public function __construct(
    ResourceMetadataFactoryInterface $resourceMetadataFactory,
    PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory,
    PropertyMetadataFactoryInterface $propertyMetadataFactory,
    ResourceClassResolverInterface $resourceClassResolver,
    OperationMethodResolverInterface $operationMethodResolver, OperationPathResolverInterface $operationPathResolver, UrlGeneratorInterface $urlGenerator = null, $filterLocator = null, NameConverterInterface $nameConverter = null, bool $oauthEnabled = false, string $oauthType = '', string $oauthFlow = '', string $oauthTokenUrl = '', string $oauthAuthorizationUrl = '', array $oauthScopes = [], array $apiKeys = [], SubresourceOperationFactoryInterface $subresourceOperationFactory = null, bool $paginationEnabled = true, string $paginationPageParameterName = 'page', bool $clientItemsPerPage = false, string $itemsPerPageParameterName = 'itemsPerPage', OperationAwareFormatsProviderInterface $formatsProvider = null, bool $paginationClientEnabled = false, string $paginationClientEnabledParameterName = 'pagination', array $defaultContext = [])
  {
    if ($urlGenerator) {
      @trigger_error(sprintf('Passing an instance of %s to %s() is deprecated since version 2.1 and will be removed in 3.0.', UrlGeneratorInterface::class, __METHOD__), E_USER_DEPRECATED);
    }

    $this->setFilterLocator($filterLocator, true);

    $this->resourceMetadataFactory = $resourceMetadataFactory;
    $this->propertyNameCollectionFactory = $propertyNameCollectionFactory;
    $this->propertyMetadataFactory = $propertyMetadataFactory;
    $this->resourceClassResolver = $resourceClassResolver;
    $this->operationMethodResolver = $operationMethodResolver;
    $this->operationPathResolver = $operationPathResolver;
    $this->nameConverter = $nameConverter;
    $this->oauthEnabled = $oauthEnabled;
    $this->oauthType = $oauthType;
    $this->oauthFlow = $oauthFlow;
    $this->oauthTokenUrl = $oauthTokenUrl;
    $this->oauthAuthorizationUrl = $oauthAuthorizationUrl;
    $this->oauthScopes = $oauthScopes;
    $this->subresourceOperationFactory = $subresourceOperationFactory;
    $this->paginationEnabled = $paginationEnabled;
    $this->paginationPageParameterName = $paginationPageParameterName;
    $this->apiKeys = $apiKeys;
    $this->subresourceOperationFactory = $subresourceOperationFactory;
    $this->clientItemsPerPage = $clientItemsPerPage;
    $this->itemsPerPageParameterName = $itemsPerPageParameterName;
    $this->formatsProvider = $formatsProvider;
    $this->paginationClientEnabled = $paginationClientEnabled;
    $this->paginationClientEnabledParameterName = $paginationClientEnabledParameterName;

    $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
  }

  /**
   * @inheritDoc
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $v3 = 3 === ($context['spec_version'] ?? $this->defaultContext['spec_version']) && !($context['api_gateway'] ?? $this->defaultContext['api_gateway']);

    $mimeTypes = $object->getMimeTypes();
    $definitions = new \ArrayObject();
    $paths = new \ArrayObject();
    $links = new \ArrayObject();

    foreach ($object->getResourceNameCollection() as $resourceClass) {
      $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
      $resourceShortName = $resourceMetadata->getShortName();

      // Items needs to be parsed first to be able to reference the lines from the collection operation
      $this->addPaths($v3, $paths, $definitions, $resourceClass, $resourceShortName, $resourceMetadata, $mimeTypes, OperationType::ITEM, $links);
      $this->addPaths($v3, $paths, $definitions, $resourceClass, $resourceShortName, $resourceMetadata, $mimeTypes, OperationType::COLLECTION, $links);
      $gg =0;
    }

    return $this->computeDoc($v3, $object, $definitions, $paths, $context);
  }

  /**
   * @inheritDoc
   */
  public function supportsNormalization($data, $format = NULL) {
    return self::FORMAT === $format && $data instanceof Documentation;
  }

  private function computeDoc(bool $v3, Documentation $documentation, \ArrayObject $definitions, \ArrayObject $paths, array $context): array
  {
    $baseUrl = $context[self::BASE_URL] ?? $this->defaultContext[self::BASE_URL];

    $docs = [
      'swagger' => self::SWAGGER_VERSION,
      'basePath' => $baseUrl,
    ];

    $docs += [
      'info' => [
        'title' => $documentation->getTitle(),
        'version' => $documentation->getVersion(),
      ],
      'paths' => $paths,
    ];

    if ('' !== $description = $documentation->getDescription()) {
      $docs['info']['description'] = $description;
    }

    return $docs;
  }

  /**
   * Updates the list of entries in the paths collection.
   */
  private function addPaths(bool $v3, \ArrayObject $paths, \ArrayObject $definitions, string $resourceClass, string $resourceShortName, ResourceMetadata $resourceMetadata, array $mimeTypes, string $operationType, \ArrayObject $links)
  {
    // ---- bon----
    $coll = $resourceMetadata->getCollectionOperations();
    $res = $resourceMetadata->getItemOperations();

    $gg = $coll = $res;
    // ---- end bon----s

    if (null === $operations = OperationType::COLLECTION === $operationType ? $resourceMetadata->getCollectionOperations() : $resourceMetadata->getItemOperations()) {
      return;
    }

    foreach ($operations as $operationName => $operation) {
      $path = $this->getPath($resourceShortName, $operationName, $operation, $operationType);
      $method = OperationType::ITEM === $operationType ? $this->operationMethodResolver->getItemOperationMethod($resourceClass, $operationName) : $this->operationMethodResolver->getCollectionOperationMethod($resourceClass, $operationName);

      $paths[$path][strtolower($method)] = $this->getPathOperation($v3, $operationName, $operation, $method, $operationType, $resourceClass, $resourceMetadata, $mimeTypes, $definitions, $links);
    }
  }

  /**
   * Gets the path for an operation.
   *
   * If the path ends with the optional _format parameter, it is removed
   * as optional path parameters are not yet supported.
   *
   * @see https://github.com/OAI/OpenAPI-Specification/issues/93
   */
  private function getPath(string $resourceShortName, string $operationName, array $operation, string $operationType): string
  {
    $path = $this->operationPathResolver->resolveOperationPath($resourceShortName, $operation, $operationType, $operationName);
    if ('.{_format}' === substr($path, -10)) {
      $path = substr($path, 0, -10);
    }

    return $path;
  }

  /**
   * Gets a path Operation Object.
   *
   * @see https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md#operation-object
   *
   * @param string[] $mimeTypes
   */
  private function getPathOperation(bool $v3, string $operationName, array $operation, string $method, string $operationType, string $resourceClass, ResourceMetadata $resourceMetadata, array $mimeTypes, \ArrayObject $definitions, \ArrayObject $links): \ArrayObject
  {
    $pathOperation = new \ArrayObject($operation[$v3 ? 'openapi_context' : 'swagger_context'] ?? []);
    $resourceShortName = $resourceMetadata->getShortName();
    $pathOperation['tags'] ?? $pathOperation['tags'] = [$resourceShortName];
    $pathOperation['operationId'] ?? $pathOperation['operationId'] = lcfirst($operationName).ucfirst($resourceShortName).ucfirst($operationType);
    if ($v3 && 'GET' === $method && OperationType::ITEM === $operationType && $link = $this->getLinkObject($resourceClass, $pathOperation['operationId'], $this->getPath($resourceShortName, $operationName, $operation, $operationType))) {
      $links[$pathOperation['operationId']] = $link;
    }
    if ($resourceMetadata->getTypedOperationAttribute($operationType, $operationName, 'deprecation_reason', null, true)) {
      $pathOperation['deprecated'] = true;
    }
    if (null !== $this->formatsProvider) {
      $responseFormats = $this->formatsProvider->getFormatsFromOperation($resourceClass, $operationName, $operationType);
      $responseMimeTypes = $this->extractMimeTypes($responseFormats);
    }
    switch ($method) {
      case 'GET':
        return $this->updateGetOperation($v3, $pathOperation, $responseMimeTypes ?? $mimeTypes, $operationType, $resourceMetadata, $resourceClass, $resourceShortName, $operationName, $definitions);
      case 'POST':
        return $this->updatePostOperation($v3, $pathOperation, $responseMimeTypes ?? $mimeTypes, $operationType, $resourceMetadata, $resourceClass, $resourceShortName, $operationName, $definitions, $links);
      case 'PATCH':
        $pathOperation['summary'] ?? $pathOperation['summary'] = sprintf('Updates the %s resource.', $resourceShortName);
      // no break
      case 'PUT':
        return $this->updatePutOperation($v3, $pathOperation, $responseMimeTypes ?? $mimeTypes, $operationType, $resourceMetadata, $resourceClass, $resourceShortName, $operationName, $definitions);
      case 'DELETE':
        return $this->updateDeleteOperation($v3, $pathOperation, $resourceShortName, $operationType, $operationName, $resourceMetadata);
    }

    return $pathOperation;
  }

  private function extractMimeTypes(array $responseFormats): array
  {
    $responseMimeTypes = [];
    foreach ($responseFormats as $mimeTypes) {
      foreach ($mimeTypes as $mimeType) {
        $responseMimeTypes[] = $mimeType;
      }
    }

    return $responseMimeTypes;
  }

  private function updateGetOperation(bool $v3, \ArrayObject $pathOperation, array $mimeTypes, string $operationType, ResourceMetadata $resourceMetadata, string $resourceClass, string $resourceShortName, string $operationName, \ArrayObject $definitions): \ArrayObject
  {
    $serializerContext = $this->getSerializerContext($operationType, false, $resourceMetadata, $operationName);

    $responseDefinitionKey = false;
    $outputMetadata = $resourceMetadata->getTypedOperationAttribute($operationType, $operationName, 'output', ['class' => $resourceClass], true);
    if (null !== $outputClass = $outputMetadata['class'] ?? null) {
      $responseDefinitionKey = $this->getDefinition($v3, $definitions, $resourceMetadata, $resourceClass, $outputClass, $serializerContext);
    }

    $successStatus = (string) $resourceMetadata->getTypedOperationAttribute($operationType, $operationName, 'status', '200');

    if (!$v3) {
      $pathOperation['produces'] ?? $pathOperation['produces'] = $mimeTypes;
    }

    if (OperationType::COLLECTION === $operationType) {
      $pathOperation['summary'] ?? $pathOperation['summary'] = sprintf('Retrieves the collection of %s resources.', $resourceShortName);

      $successResponse = ['description' => sprintf('%s collection response', $resourceShortName)];

      if ($responseDefinitionKey) {
        if ($v3) {
          $successResponse['content'] = array_fill_keys($mimeTypes, [
            'schema' => [
              'type' => 'array',
              'items' => ['$ref' => sprintf('#/components/schemas/%s', $responseDefinitionKey)],
            ],
          ]);
        } else {
          $successResponse['schema'] = [
            'type' => 'array',
            'items' => ['$ref' => sprintf('#/definitions/%s', $responseDefinitionKey)],
          ];
        }
      }

      $pathOperation['responses'] ?? $pathOperation['responses'] = [$successStatus => $successResponse];
      $pathOperation['parameters'] ?? $pathOperation['parameters'] = $this->getFiltersParameters($v3, $resourceClass, $operationName, $resourceMetadata, $definitions, $serializerContext);

      if ($this->paginationEnabled && $resourceMetadata->getCollectionOperationAttribute($operationName, 'pagination_enabled', true, true)) {
        $paginationParameter = [
          'name' => $this->paginationPageParameterName,
          'in' => 'query',
          'required' => false,
          'description' => 'The collection page number',
        ];
        $v3 ? $paginationParameter['schema'] = ['type' => 'integer'] : $paginationParameter['type'] = 'integer';
        $pathOperation['parameters'][] = $paginationParameter;

        if ($resourceMetadata->getCollectionOperationAttribute($operationName, 'pagination_client_items_per_page', $this->clientItemsPerPage, true)) {
          $itemPerPageParameter = [
            'name' => $this->itemsPerPageParameterName,
            'in' => 'query',
            'required' => false,
            'description' => 'The number of items per page',
          ];
          $v3 ? $itemPerPageParameter['schema'] = ['type' => 'integer'] : $itemPerPageParameter['type'] = 'integer';

          $pathOperation['parameters'][] = $itemPerPageParameter;
        }
      }

      if ($this->paginationEnabled && $resourceMetadata->getCollectionOperationAttribute($operationName, 'pagination_client_enabled', $this->paginationClientEnabled, true)) {
        $paginationEnabledParameter = [
          'name' => $this->paginationClientEnabledParameterName,
          'in' => 'query',
          'required' => false,
          'description' => 'Enable or disable pagination',
        ];
        $v3 ? $paginationEnabledParameter['schema'] = ['type' => 'boolean'] : $paginationEnabledParameter['type'] = 'boolean';
        $pathOperation['parameters'][] = $paginationEnabledParameter;
      }

      return $pathOperation;
    }

    $pathOperation['summary'] ?? $pathOperation['summary'] = sprintf('Retrieves a %s resource.', $resourceShortName);

    $pathOperation = $this->addItemOperationParameters($v3, $pathOperation);

    $successResponse = ['description' => sprintf('%s resource response', $resourceShortName)];
    if ($responseDefinitionKey) {
      if ($v3) {
        $successResponse['content'] = array_fill_keys($mimeTypes, ['schema' => ['$ref' => sprintf('#/components/schemas/%s', $responseDefinitionKey)]]);
      } else {
        $successResponse['schema'] = ['$ref' => sprintf('#/definitions/%s', $responseDefinitionKey)];
      }
    }

    $pathOperation['responses'] ?? $pathOperation['responses'] = [
      $successStatus => $successResponse,
      '404' => ['description' => 'Resource not found'],
    ];

    return $pathOperation;
  }

  private function getSerializerContext(string $operationType, bool $denormalization, ResourceMetadata $resourceMetadata, string $operationName): ?array
  {
    $contextKey = $denormalization ? 'denormalization_context' : 'normalization_context';

    if (OperationType::COLLECTION === $operationType) {
      return $resourceMetadata->getCollectionOperationAttribute($operationName, $contextKey, null, true);
    }

    return $resourceMetadata->getItemOperationAttribute($operationName, $contextKey, null, true);
  }

  private function getDefinition(bool $v3, \ArrayObject $definitions, ResourceMetadata $resourceMetadata, string $resourceClass, ?string $publicClass, array $serializerContext = null): string
  {
    $keyPrefix = $resourceMetadata->getShortName();
    if (null !== $publicClass && $resourceClass !== $publicClass) {
      $keyPrefix .= ':'.md5($publicClass);
    }

    if (isset($serializerContext[self::SWAGGER_DEFINITION_NAME])) {
      $definitionKey = sprintf('%s-%s', $keyPrefix, $serializerContext[self::SWAGGER_DEFINITION_NAME]);
    } else {
      $definitionKey = $this->getDefinitionKey($keyPrefix, (array) ($serializerContext[AbstractNormalizer::GROUPS] ?? []));
    }

    if (!isset($definitions[$definitionKey])) {
      $definitions[$definitionKey] = [];  // Initialize first to prevent infinite loop
      $definitions[$definitionKey] = $this->getDefinitionSchema($v3, $publicClass ?? $resourceClass, $resourceMetadata, $definitions, $serializerContext);
    }

    return $definitionKey;
  }

  private function getDefinitionKey(string $resourceShortName, array $groups): string
  {
    return $groups ? sprintf('%s-%s', $resourceShortName, implode('_', $groups)) : $resourceShortName;
  }

  /**
   * Gets a definition Schema Object.
   *
   * @see https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md#schemaObject
   */
  private function getDefinitionSchema(bool $v3, string $resourceClass, ResourceMetadata $resourceMetadata, \ArrayObject $definitions, array $serializerContext = null): \ArrayObject
  {
    $definitionSchema = new \ArrayObject(['type' => 'object']);

    if (null !== $description = $resourceMetadata->getDescription()) {
      $definitionSchema['description'] = $description;
    }

    if (null !== $iri = $resourceMetadata->getIri()) {
      $definitionSchema['externalDocs'] = ['url' => $iri];
    }

    $options = isset($serializerContext[AbstractNormalizer::GROUPS]) ? ['serializer_groups' => $serializerContext[AbstractNormalizer::GROUPS]] : [];
    //    foreach ($this->propertyNameCollectionFactory->create($resourceClass, $options) as $propertyName) {
    //      $propertyMetadata = $this->propertyMetadataFactory->create($resourceClass, $propertyName);
    //      if (!$propertyMetadata->isReadable() && !$propertyMetadata->isWritable()) {
    //        continue;
    //      }
    //
    //      $normalizedPropertyName = $this->nameConverter ? $this->nameConverter->normalize($propertyName, $resourceClass, self::FORMAT, $serializerContext ?? []) : $propertyName;
    //      if ($propertyMetadata->isRequired()) {
    //        $definitionSchema['required'][] = $normalizedPropertyName;
    //      }
    //
    //      $definitionSchema['properties'][$normalizedPropertyName] = $this->getPropertySchema($v3, $propertyMetadata, $definitions, $serializerContext);
    //    }

    return $definitionSchema;
  }

  private function addItemOperationParameters(bool $v3, \ArrayObject $pathOperation): \ArrayObject
  {
    $parameter = [
      'name' => 'id',
      'in' => 'path',
      'required' => true,
    ];
    $v3 ? $parameter['schema'] = ['type' => 'string'] : $parameter['type'] = 'string';
    $pathOperation['parameters'] ?? $pathOperation['parameters'] = [$parameter];

    return $pathOperation;
  }

  private function updateDeleteOperation(bool $v3, \ArrayObject $pathOperation, string $resourceShortName, string $operationType, string $operationName, ResourceMetadata $resourceMetadata): \ArrayObject
  {
    $pathOperation['summary'] ?? $pathOperation['summary'] = sprintf('Removes the %s resource.', $resourceShortName);
    $pathOperation['responses'] ?? $pathOperation['responses'] = [
      (string) $resourceMetadata->getTypedOperationAttribute($operationType, $operationName, 'status', '204') => ['description' => sprintf('%s resource deleted', $resourceShortName)],
      '404' => ['description' => 'Resource not found'],
    ];

    return $this->addItemOperationParameters($v3, $pathOperation);
  }

  private function updatePutOperation(bool $v3, \ArrayObject $pathOperation, array $mimeTypes, string $operationType, ResourceMetadata $resourceMetadata, string $resourceClass, string $resourceShortName, string $operationName, \ArrayObject $definitions): \ArrayObject
  {
    if (!$v3) {
      $pathOperation['consumes'] ?? $pathOperation['consumes'] = $mimeTypes;
      $pathOperation['produces'] ?? $pathOperation['produces'] = $mimeTypes;
    }

    $pathOperation['summary'] ?? $pathOperation['summary'] = sprintf('Replaces the %s resource.', $resourceShortName);

    $pathOperation = $this->addItemOperationParameters($v3, $pathOperation);

    $responseDefinitionKey = false;
    $outputMetadata = $resourceMetadata->getTypedOperationAttribute($operationType, $operationName, 'output', ['class' => $resourceClass], true);
    if (null !== $outputClass = $outputMetadata['class'] ?? null) {
      $responseDefinitionKey = $this->getDefinition($v3, $definitions, $resourceMetadata, $resourceClass, $outputClass, $this->getSerializerContext($operationType, false, $resourceMetadata, $operationName));
    }

    $successResponse = ['description' => sprintf('%s resource updated', $resourceShortName)];
    if ($responseDefinitionKey) {
      if ($v3) {
        $successResponse['content'] = array_fill_keys($mimeTypes, ['schema' => ['$ref' => sprintf('#/components/schemas/%s', $responseDefinitionKey)]]);
      } else {
        $successResponse['schema'] = ['$ref' => sprintf('#/definitions/%s', $responseDefinitionKey)];
      }
    }

    $pathOperation['responses'] ?? $pathOperation['responses'] = [
      (string) $resourceMetadata->getTypedOperationAttribute($operationType, $operationName, 'status', '200') => $successResponse,
      '400' => ['description' => 'Invalid input'],
      '404' => ['description' => 'Resource not found'],
    ];

    $inputMetadata = $resourceMetadata->getTypedOperationAttribute($operationType, $operationName, 'input', ['class' => $resourceClass], true);
    if (null === $inputClass = $inputMetadata['class'] ?? null) {
      return $pathOperation;
    }

    $requestDefinitionKey = $this->getDefinition($v3, $definitions, $resourceMetadata, $resourceClass, $inputClass, $this->getSerializerContext($operationType, true, $resourceMetadata, $operationName));
    if ($v3) {
      $pathOperation['requestBody'] ?? $pathOperation['requestBody'] = [
        'content' => array_fill_keys($mimeTypes, ['schema' => ['$ref' => sprintf('#/components/schemas/%s', $requestDefinitionKey)]]),
        'description' => sprintf('The updated %s resource', $resourceShortName),
      ];
    } else {
      $pathOperation['parameters'][] = [
        'name' => lcfirst($resourceShortName),
        'in' => 'body',
        'description' => sprintf('The updated %s resource', $resourceShortName),
        'schema' => ['$ref' => sprintf('#/definitions/%s', $requestDefinitionKey)],
      ];
    }

    return $pathOperation;
  }

  /**
   * Gets parameters corresponding to enabled filters.
   */
  private function getFiltersParameters(bool $v3, string $resourceClass, string $operationName, ResourceMetadata $resourceMetadata, \ArrayObject $definitions, array $serializerContext = null): array
  {
    if (null === $this->filterLocator) {
      return [];
    }

    $parameters = [];
    $resourceFilters = $resourceMetadata->getCollectionOperationAttribute($operationName, 'filters', [], true);
    foreach ($resourceFilters as $filterId) {
      if (!$filter = $this->getFilter($filterId)) {
        continue;
      }

      foreach ($filter->getDescription($resourceClass) as $name => $data) {
        $parameter = [
          'name' => $name,
          'in' => 'query',
          'required' => $data['required'],
        ];

        $type = $this->getType($v3, $data['type'], $data['is_collection'] ?? false, null, null, $definitions, $serializerContext);
        $v3 ? $parameter['schema'] = $type : $parameter += $type;

        if ('array' === $type['type'] ?? '') {
          $deepObject = \in_array($data['type'], [Type::BUILTIN_TYPE_ARRAY, Type::BUILTIN_TYPE_OBJECT], true);

          if ($v3) {
            $parameter['style'] = $deepObject ? 'deepObject' : 'form';
            $parameter['explode'] = true;
          } else {
            $parameter['collectionFormat'] = $deepObject ? 'csv' : 'multi';
          }
        }

        $key = $v3 ? 'openapi' : 'swagger';
        if (isset($data[$key])) {
          $parameter = $data[$key] + $parameter;
        }

        $parameters[] = $parameter;
      }
    }

    return $parameters;
  }

  private function updatePostOperation(bool $v3, \ArrayObject $pathOperation, array $mimeTypes, string $operationType, ResourceMetadata $resourceMetadata, string $resourceClass, string $resourceShortName, string $operationName, \ArrayObject $definitions, \ArrayObject $links): \ArrayObject
  {
    if (!$v3) {
      $pathOperation['consumes'] ?? $pathOperation['consumes'] = $mimeTypes;
      $pathOperation['produces'] ?? $pathOperation['produces'] = $mimeTypes;
    }

    $pathOperation['summary'] ?? $pathOperation['summary'] = sprintf('Creates a %s resource.', $resourceShortName);

    $userDefinedParameters = $pathOperation['parameters'] ?? null;
    if (OperationType::ITEM === $operationType) {
      $pathOperation = $this->addItemOperationParameters($v3, $pathOperation);
    }

    $responseDefinitionKey = false;
    $outputMetadata = $resourceMetadata->getTypedOperationAttribute($operationType, $operationName, 'output', ['class' => $resourceClass], true);
    if (null !== $outputClass = $outputMetadata['class'] ?? null) {
      $responseDefinitionKey = $this->getDefinition($v3, $definitions, $resourceMetadata, $resourceClass, $outputClass, $this->getSerializerContext($operationType, false, $resourceMetadata, $operationName));
    }

    $successResponse = ['description' => sprintf('%s resource created', $resourceShortName)];
    if ($responseDefinitionKey) {
      if ($v3) {
        $successResponse['content'] = array_fill_keys($mimeTypes, ['schema' => ['$ref' => sprintf('#/components/schemas/%s', $responseDefinitionKey)]]);
        if ($links[$key = 'get'.ucfirst($resourceShortName).ucfirst(OperationType::ITEM)] ?? null) {
          $successResponse['links'] = [ucfirst($key) => $links[$key]];
        }
      } else {
        $successResponse['schema'] = ['$ref' => sprintf('#/definitions/%s', $responseDefinitionKey)];
      }
    }

    $pathOperation['responses'] ?? $pathOperation['responses'] = [
      (string) $resourceMetadata->getTypedOperationAttribute($operationType, $operationName, 'status', '201') => $successResponse,
      '400' => ['description' => 'Invalid input'],
      '404' => ['description' => 'Resource not found'],
    ];

    $inputMetadata = $resourceMetadata->getTypedOperationAttribute($operationType, $operationName, 'input', ['class' => $resourceClass], true);
    if (null === $inputClass = $inputMetadata['class'] ?? null) {
      return $pathOperation;
    }

    $requestDefinitionKey = $this->getDefinition($v3, $definitions, $resourceMetadata, $resourceClass, $inputClass, $this->getSerializerContext($operationType, true, $resourceMetadata, $operationName));
    if ($v3) {
      $pathOperation['requestBody'] ?? $pathOperation['requestBody'] = [
        'content' => array_fill_keys($mimeTypes, ['schema' => ['$ref' => sprintf('#/components/schemas/%s', $requestDefinitionKey)]]),
        'description' => sprintf('The new %s resource', $resourceShortName),
      ];
    } else {
      $userDefinedParameters ?? $pathOperation['parameters'][] = [
        'name' => lcfirst($resourceShortName),
        'in' => 'body',
        'description' => sprintf('The new %s resource', $resourceShortName),
        'schema' => ['$ref' => sprintf('#/definitions/%s', $requestDefinitionKey)],
      ];
    }

    return $pathOperation;
  }


}
