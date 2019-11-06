<?php


namespace Drupal\api_platform\Core\JsonLd;


use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Drupal\api_platform\Core\Api\UrlGeneratorInterface;
use Drupal\api_platform\Core\Exception\ResourceClassNotFoundException;
use Drupal\api_platform\Core\JsonLd\AnonymousContextBuilderInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class ContextBuilder implements AnonymousContextBuilderInterface {

  public const FORMAT = 'jsonld';

  private $resourceNameCollectionFactory;
  private $resourceMetadataFactory;
  private $propertyNameCollectionFactory;
  private $propertyMetadataFactory;
  private $urlGenerator;

  /**
   * @var NameConverterInterface|null
   */
  private $nameConverter;

  public function __construct(ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory, ResourceMetadataFactoryInterface $resourceMetadataFactory, PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory, PropertyMetadataFactoryInterface $propertyMetadataFactory, \Drupal\api_platform\Core\Api\UrlGeneratorInterface $urlGenerator, NameConverterInterface $nameConverter = null)
  {
    $this->resourceNameCollectionFactory = $resourceNameCollectionFactory;
    $this->resourceMetadataFactory = $resourceMetadataFactory;
    $this->propertyNameCollectionFactory = $propertyNameCollectionFactory;
    $this->propertyMetadataFactory = $propertyMetadataFactory;
    $this->urlGenerator = $urlGenerator;
    $this->nameConverter = $nameConverter;
  }

  /**
   * @inheritDoc
   */
  public function getAnonymousResourceContext(
    $object,
    array $context = [],
    int $referenceType = UrlGeneratorInterface::ABS_PATH
  ): array {
    // TODO: Implement getAnonymousResourceContext() method.
  }

  /**
   * @inheritDoc
   */
  public function getBaseContext(
    int $referenceType = UrlGeneratorInterface::ABS_PATH
  ): array {
    // TODO: Implement getBaseContext() method.
  }

  /**
   * @inheritDoc
   */
  public function getEntrypointContext(
    int $referenceType = UrlGeneratorInterface::ABS_PATH
  ): array {
    // TODO: Implement getEntrypointContext() method.
  }

  /**
   * @inheritDoc
   */
  public function getResourceContext(
    string $resourceClass,
    int $referenceType = UrlGeneratorInterface::ABS_PATH
  ): array {
    // TODO: Implement getResourceContext() method.
  }

  /**
   * @inheritDoc
   */
  public function getResourceContextUri(
    string $resourceClass,
    int $referenceType = UrlGeneratorInterface::ABS_PATH
  ): string {
    $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);

    return $this->urlGenerator->generate('api_platform.api_jsonld_context', ['shortName' => $resourceMetadata->getShortName()], $referenceType);
  }

}
