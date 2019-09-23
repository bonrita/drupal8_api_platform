<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Swagger\Serializer;

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
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
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

}
