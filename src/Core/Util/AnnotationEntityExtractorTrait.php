<?php


namespace Drupal\api_platform\Core\Util;


use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Inflector\Inflector;
use Drupal\api_platform\Core\Annotation\ApiEntity;

trait AnnotationEntityExtractorTrait {

  /**
   * Filters annotations to get back only ApiEntity annotations.
   *
   * @param array $miscAnnotations class or property annotations
   *
   * @return \Iterator only ApiEntity annotations
   */
  private function getDrupalAnnotations(array $miscAnnotations): \Iterator
  {
    foreach ($miscAnnotations as $miscAnnotation) {
      if (ApiEntity::class === \get_class($miscAnnotation)) {
        yield $miscAnnotation;
      }
    }
  }


  private function readDrupalAnnotations(
    \ReflectionClass $reflectionClass,
    Reader $reader
  ) {

    $wrappers = [];

    foreach ($this->getDrupalAnnotations($reader->getClassAnnotations($reflectionClass)) as $entityAnnotation) {
      $entityClass = $entityAnnotation->entityClass;
      $id = $this->generateEntityClassId($reflectionClass, $entityClass);

      if (!isset($filters[$id])) {
        $wrappers[$reflectionClass->getName()]['class'] = $entityClass;
      }
    }

    return $wrappers;
  }

  /**
   * Generates a unique, per-class and per-filter identifier prefixed by `annotated_`.
   *
   * @param \ReflectionClass $reflectionClass the reflection class of a Resource
   * @param string           $entityClass     the filter class
   */
  private function generateEntityClassId(\ReflectionClass $reflectionClass, string $entityClass): string
  {
    return 'annotated_'.Inflector::tableize(str_replace('\\', '', $reflectionClass->getName().(new \ReflectionClass($entityClass))->getName()));
  }

}
