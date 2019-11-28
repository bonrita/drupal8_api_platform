<?php


namespace Drupal\api_platform\Core\PropertyInfo\Extractor;


use Doctrine\Common\Annotations\Reader;
use Drupal\api_platform\ApiEntity\ApiEntityFieldDescriptionInterface;
use Drupal\api_platform\Core\Annotation\ApiEntity;
use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\api_platform\PropertyInfo\Extractor\EntityExtractor;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyDescriptionExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;
use function GuzzleHttp\Psr7\str;

class EntityPropertyInfoExtractor implements PropertyDescriptionExtractorInterface, PropertyTypeExtractorInterface {


  /**
   * @var \Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor
   */
  private $decorated;

  /**
   * @var \Doctrine\Common\Annotations\Reader
   */
  private $reader;

  /**
   * @var \Drupal\api_platform\Core\Api\ResourceClassResolverInterface
   */
  private $resourceClassResolver;

  /**
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  private $classResolver;

  /**
   * @var \Drupal\api_platform\PropertyInfo\Extractor\EntityExtractor
   */
  private $entityExtractor;

  public function __construct(PhpDocExtractor $decorated, Reader $reader, ResourceClassResolverInterface $resourceClassResolver, ClassResolverInterface $classResolver, EntityExtractor $entityExtractor) {
    $this->decorated = $decorated;
    $this->reader = $reader;
    $this->resourceClassResolver = $resourceClassResolver;
    $this->classResolver = $classResolver;
    $this->entityExtractor = $entityExtractor;
  }

  /**
   * @inheritDoc
   */
  public function getShortDescription($class, $property, array $context = []) {
    $description =  $this->decorated->getShortDescription($class, $property, $context);

    $interfaces = class_implements($class);
    if (NULL == $description && in_array(ApiEntityFieldDescriptionInterface::class, $interfaces) && isset($context['entity_class']) && is_bool($context['entity_class'])
    && $this->reader->getClassAnnotation(new \ReflectionClass($class), ApiEntity::class)) {
      $context['entity_type'] = $this->resourceClassResolver->getEntityTypeId($class);
      $apiEntity = $this->classResolver->getInstanceFromDefinition($class, $property);
      $fieldDescription = $apiEntity->accessFieldDescription($context, $property);
      $description = $fieldDescription ?? $description;
//      $reflectionMethod->invoke()
    }

    return $description;
  }

  /**
   * @inheritDoc
   */
  public function getLongDescription($class, $property, array $context = []) {
    return $this->decorated->getLongDescription($class, $property, $context);
  }

  /**
   * @inheritDoc
   */
  public function getTypes($class, $property, array $context = []) {
    $this->entityExtractor->getTypes($class, $property, $context);
  }


}
