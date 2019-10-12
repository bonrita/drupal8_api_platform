<?php


namespace Drupal\api_platform\Core\Metadata\Resource\Factory;


use Drupal\api_platform\Core\Annotation\ApiResource;
use Drupal\api_platform\Core\Exception\ResourceClassNotFoundException;
use Drupal\api_platform\Core\Metadata\Resource\ResourceMetadata;
use Doctrine\Common\Annotations\Reader;

class AnnotationResourceMetadataFactory implements ResourceMetadataFactoryInterface {
  private $reader;
  private $decorated;

  public function __construct(Reader $reader, ResourceMetadataFactoryInterface $decorated = null)
  {
    $this->reader = $reader;
    $this->decorated = $decorated;
  }

  /**
   * {@inheritdoc}
   */
  public function create(string $resourceClass): ResourceMetadata
  {
    $parentResourceMetadata = null;
    if ($this->decorated) {
      try {
        $parentResourceMetadata = $this->decorated->create($resourceClass);
      } catch (ResourceClassNotFoundException $resourceNotFoundException) {
        // Ignore not found exception from decorated factories
      }
    }

    try {
      $reflectionClass = new \ReflectionClass($resourceClass);
    } catch (\ReflectionException $reflectionException) {
      return $this->handleNotFound($parentResourceMetadata, $resourceClass);
    }

    $resourceAnnotation = $this->reader->getClassAnnotation($reflectionClass, ApiResource::class);
    if (!$resourceAnnotation instanceof ApiResource) {
      return $this->handleNotFound($parentResourceMetadata, $resourceClass);
    }

    return $this->createMetadata($resourceAnnotation, $parentResourceMetadata);
  }

  /**
   * Returns the metadata from the decorated factory if available or throws an exception.
   *
   * @throws ResourceClassNotFoundException
   */
  private function handleNotFound(?ResourceMetadata $parentPropertyMetadata, string $resourceClass): ResourceMetadata
  {
    if (null !== $parentPropertyMetadata) {
      return $parentPropertyMetadata;
    }

    throw new ResourceClassNotFoundException(sprintf('Resource "%s" not found.', $resourceClass));
  }

  private function createMetadata(ApiResource $annotation, ResourceMetadata $parentResourceMetadata = null): ResourceMetadata
  {
    if (!$parentResourceMetadata) {
      return new ResourceMetadata(
        $annotation->shortName,
        $annotation->description,
        $annotation->iri,
        $annotation->itemOperations,
        $annotation->collectionOperations,
        $annotation->attributes,
        $annotation->subresourceOperations,
        $annotation->graphql
      );
    }

    $resourceMetadata = $parentResourceMetadata;
    foreach (['shortName', 'description', 'iri', 'itemOperations', 'collectionOperations', 'subresourceOperations', 'graphql', 'attributes'] as $property) {
      $resourceMetadata = $this->createWith($resourceMetadata, $property, $annotation->{$property});
    }

    return $resourceMetadata;
  }

  /**
   * Creates a new instance of metadata if the property is not already set.
   */
  private function createWith(ResourceMetadata $resourceMetadata, string $property, $value): ResourceMetadata
  {
    $upperProperty = ucfirst($property);
    $getter = "get$upperProperty";

    if (null !== $resourceMetadata->{$getter}()) {
      return $resourceMetadata;
    }

    $wither = "with$upperProperty";

    return $resourceMetadata->{$wither}($value);
  }

}
