<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Resource\Factory;


use Drupal\api_platform\Core\Metadata\Extractor\ExtractorInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Exception\ResourceClassNotFoundException;
use Drupal\api_platform\Core\Metadata\Resource\ResourceMetadata;

final class ExtractorResourceMetadataFactory implements ResourceMetadataFactoryInterface {

  private $extractor;
  private $decorated;

  public function __construct(ExtractorInterface $extractor, ResourceMetadataFactoryInterface $decorated = null)
  {
    $this->extractor = $extractor;
    $this->decorated = $decorated;
  }

  /**
   * Creates a resource metadata.
   *
   * @throws ResourceClassNotFoundException
   */
  public function create(string $resourceClass): ResourceMetadata {
    $parentResourceMetadata = null;
    if ($this->decorated) {
      try {
        $parentResourceMetadata = $this->decorated->create($resourceClass);
      } catch (ResourceClassNotFoundException $resourceNotFoundException) {
        // Ignore not found exception from decorated factories
      }
    }

    if (!(class_exists($resourceClass) || interface_exists($resourceClass)) || !$resource = $this->extractor->getResources()[$resourceClass] ?? false) {
      return $this->handleNotFound($parentResourceMetadata, $resourceClass);
    }

    return $this->update($parentResourceMetadata ?: new ResourceMetadata(), $resource);
  }

  /**
   * Returns the metadata from the decorated factory if available or throws an exception.
   *
   * @throws Drupal\api_platform\Core\Exception\ResourceClassNotFoundException
   */
  private function handleNotFound(?ResourceMetadata $parentPropertyMetadata, string $resourceClass): ResourceMetadata
  {
    if (null !== $parentPropertyMetadata) {
      return $parentPropertyMetadata;
    }

    throw new ResourceClassNotFoundException(sprintf('Resource "%s" not found.', $resourceClass));
  }

  /**
   * Creates a new instance of metadata if the property is not already set.
   */
  private function update(ResourceMetadata $resourceMetadata, array $metadata): ResourceMetadata
  {
    foreach (['shortName', 'description', 'iri', 'itemOperations', 'collectionOperations', 'subresourceOperations', 'graphql', 'attributes'] as $property) {
      if (null === $metadata[$property] || null !== $resourceMetadata->{'get'.ucfirst($property)}()) {
        continue;
      }

      $resourceMetadata = $resourceMetadata->{'with'.ucfirst($property)}($metadata[$property]);
    }

    return $resourceMetadata;
  }


}
