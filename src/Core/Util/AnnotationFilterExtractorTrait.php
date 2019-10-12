<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Util;

use Drupal\api_platform\Core\Annotation\ApiFilter;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Inflector\Inflector;

/**
 * Generates a service id for a generic filter.
 *
 * @internal
 *
 */
trait AnnotationFilterExtractorTrait
{
  /**
   * Filters annotations to get back only ApiFilter annotations.
   *
   * @param array $miscAnnotations class or property annotations
   *
   * @return \Iterator only ApiFilter annotations
   */
  private function getFilterAnnotations(array $miscAnnotations): \Iterator
  {
    foreach ($miscAnnotations as $miscAnnotation) {
      if (ApiFilter::class === \get_class($miscAnnotation)) {
        yield $miscAnnotation;
      }
    }
  }

  /**
   * Reads filter annotations from a ReflectionClass.
   *
   * @return array Key is the filter id. It has two values, properties and the ApiFilter instance
   */
  private function readFilterAnnotations(\ReflectionClass $reflectionClass, Reader $reader): array
  {
    $filters = [];

    foreach ($this->getFilterAnnotations($reader->getClassAnnotations($reflectionClass)) as $filterAnnotation) {
      $filterClass = $filterAnnotation->filterClass;
      $id = $this->generateFilterId($reflectionClass, $filterClass, $filterAnnotation->id);

      if (!isset($filters[$id])) {
        $filters[$id] = [$filterAnnotation->arguments, $filterClass];
      }

      if ($properties = $this->getFilterProperties($filterAnnotation, $reflectionClass)) {
        $filters[$id][0]['properties'] = $properties;
      }
    }

    foreach ($reflectionClass->getProperties() as $reflectionProperty) {
      foreach ($this->getFilterAnnotations($reader->getPropertyAnnotations($reflectionProperty)) as $filterAnnotation) {
        $filterClass = $filterAnnotation->filterClass;
        $id = $this->generateFilterId($reflectionClass, $filterClass, $filterAnnotation->id);

        if (!isset($filters[$id])) {
          $filters[$id] = [$filterAnnotation->arguments, $filterClass];
        }

        if ($properties = $this->getFilterProperties($filterAnnotation, $reflectionClass, $reflectionProperty)) {
          if (isset($filters[$id][0]['properties'])) {
            $filters[$id][0]['properties'] = array_merge($filters[$id][0]['properties'], $properties);
          } else {
            $filters[$id][0]['properties'] = $properties;
          }
        }
      }
    }

    $parent = $reflectionClass->getParentClass();

    if (false !== $parent) {
      return array_merge($filters, $this->readFilterAnnotations($parent, $reader));
    }

    return $filters;
  }


}
