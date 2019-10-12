<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Resource\Factory;


use Drupal\api_platform\Core\Annotation\ApiResource;
use Drupal\api_platform\Core\Metadata\Resource\ResourceNameCollection;
use Drupal\api_platform\Core\Util\ReflectionClassRecursiveIterator;
use Doctrine\Common\Annotations\Reader;

/**
 * Class AnnotationResourceNameCollectionFactory
 *
 * @package ApiPlatform\Core\Metadata\Resource\Factory
 *
 * Creates a resource name collection from {@see ApiResource} annotations.
 *
 */
final class AnnotationResourceNameCollectionFactory implements ResourceNameCollectionFactoryInterface {

  private $reader;
  private $paths;
  private $decorated;

  /**
   * @param string[] $paths
   */
  public function __construct(Reader $reader, array $paths, ResourceNameCollectionFactoryInterface $decorated = null)
  {
    $this->reader = $reader;
    $this->paths = $paths;
    $this->decorated = $decorated;
  }

  /**
   * @inheritDoc
   */
  public function create(): ResourceNameCollection {
    $classes = [];

    if ($this->decorated) {
      foreach ($this->decorated->create() as $resourceClass) {
        $classes[$resourceClass] = TRUE;
      }
    }

    foreach (ReflectionClassRecursiveIterator::getReflectionClassesFromDirectories($this->paths) as $className => $reflectionClass) {
      if ($this->reader->getClassAnnotation($reflectionClass, ApiResource::class)) {
        $classes[$className] = true;
      }
    }

    return new ResourceNameCollection(array_keys($classes));
  }

}
