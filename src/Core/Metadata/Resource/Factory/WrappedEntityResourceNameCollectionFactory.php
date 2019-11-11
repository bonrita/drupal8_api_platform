<?php


namespace Drupal\api_platform\Core\Metadata\Resource\Factory;


use Doctrine\Common\Annotations\Reader;
use Drupal\api_platform\Core\Metadata\Resource\ResourceNameCollection;
use Drupal\api_platform\Core\Util\AnnotationEntityExtractorTrait;

class WrappedEntityResourceNameCollectionFactory implements ResourceNameCollectionFactoryInterface {
 use AnnotationEntityExtractorTrait;

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
        $reflectionClass = new \ReflectionClass($resourceClass);
        $drupalAttribs = $this->readDrupalAnnotations($reflectionClass, $this->reader);

        if (isset($drupalAttribs[$resourceClass]['class'])) {
          $classes[$drupalAttribs[$resourceClass]['class']] = TRUE;
        } else {
          $classes[$resourceClass] = TRUE;
        }

      }
    }

    return new ResourceNameCollection(array_keys($classes));
  }

}
