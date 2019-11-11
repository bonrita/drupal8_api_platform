<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

use Doctrine\Common\Annotations\Reader;
use Drupal\api_platform\Core\Annotation\ApiEntity;
use Drupal\api_platform\Core\Annotation\ApiResource;
use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Drupal\api_platform\Core\Util\AnnotationEntityExtractorTrait;
use Drupal\api_platform\Core\Util\ReflectionClassRecursiveIterator;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Class ResourceEntityClassResolver
 *
 * Resolves or gets the corresponding ApiResource class from a Drupal
 * entity class.
 *
 * @package Drupal\api_platform\Core\Api
 */
class ResourceEntityClassResolver implements ResourceEntityClassResolverInterface {

  use AnnotationEntityExtractorTrait;

  private $reader;
  private $paths;

  /**
   * @param string[] $paths
   */
  public function __construct(Reader $reader, array $paths)
  {
    $this->reader = $reader;
    $this->paths = $paths;
  }

  /**
   * {@inheritDoc}
   */
  public function getClassFromObject(ContentEntityInterface $entity): ?string {
    $class = NULL;

    foreach (ReflectionClassRecursiveIterator::getReflectionClassesFromDirectories($this->paths) as $className => $reflectionClass) {
      if ($this->reader->getClassAnnotation($reflectionClass, ApiEntity::class)) {
        $drupalAttribs = $this->readDrupalAnnotations($reflectionClass, $this->reader);

        if (isset($drupalAttribs[$className]['class']) && is_a($entity, $drupalAttribs[$className]['class'])) {
          $class = $className;
          break;
        }
      }
    }

    if (!$class) {
      throw new InvalidArgumentException(sprintf('Object of type "%s" does not match any ApiResource class.', get_class($entity)));
    }

    return $class;

  }

}
