<?php


namespace Drupal\api_platform\Core\Metadata\Resource\Factory;


use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Inflector\Inflector;
use Drupal\api_platform\Core\Annotation\ApiEntity;
use Drupal\api_platform\Core\Exception\ResourceClassNotFoundException;
use Drupal\api_platform\Core\Metadata\Resource\ResourceMetadata;
use Drupal\api_platform\Core\Util\AnnotationEntityExtractorTrait;

/**
 * Class WrappedEntityResourceMetadataFactory
 *
 * Get information whether the class wraps a Drupal entity.
 *
 * @package Drupal\api_platform\Core\Metadata\Resource\Factory
 */
class WrappedEntityResourceMetadataFactory implements ResourceMetadataFactoryInterface {

  use AnnotationEntityExtractorTrait;

  private $decorated;
  private $reader;

  public function __construct(Reader $reader, ResourceMetadataFactoryInterface $decorated) {
    $this->decorated = $decorated;
    $this->reader = $reader;
  }

  /**
   * @inheritDoc
   */
  public function create(string $resourceClass): ResourceMetadata {
    $resourceMetadata = NULL;

    if ($this->decorated) {
      try {
        $resourceMetadata = $this->decorated->create($resourceClass);
      } catch (ResourceClassNotFoundException $resourceNotFoundException) {
        // Ignore not found exception from decorated factories
      }
    }

    if (null === $resourceMetadata) {
      return $this->handleNotFound($resourceMetadata, $resourceClass);
    }

    try {
      $reflectionClass = new \ReflectionClass($resourceClass);
    } catch (\ReflectionException $reflectionException) {
      return $this->handleNotFound($resourceMetadata, $resourceClass);
    }

    $drupalAttribs = $this->readDrupalAnnotations($reflectionClass, $this->reader);

    if (!$drupalAttribs) {
      return $resourceMetadata;
    }

    $attributes = $resourceMetadata->getAttributes();

    if (!$attributes) {
      $attributes = [];
    }

    return $resourceMetadata->withAttributes(array_merge($attributes, ['drupal_attribs' => $drupalAttribs]));
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

}
