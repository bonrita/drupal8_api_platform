<?php


namespace Drupal\api_platform\Core\Serializer\Mapping\Loader;


use Doctrine\Common\Annotations\Reader;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Exception\MappingException;
use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\Serializer\Mapping\ClassMetadataInterface;
use Symfony\Component\Serializer\Mapping\Loader\LoaderInterface;

class AnnotationLoader implements LoaderInterface {

  private $reader;

  /**
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  private $classResolver;

  public function __construct(Reader $reader, ClassResolverInterface $classResolver)
  {
    $this->reader = $reader;
    $this->classResolver = $classResolver;
  }

  /**
   * @inheritDoc
   */
  public function loadClassMetadata(ClassMetadataInterface $classMetadata) {
    $reflectionClass = $classMetadata->getReflectionClass();
    $className = $reflectionClass->name;
    $loaded = false;

    $attributesMetadata = $classMetadata->getAttributesMetadata();

    foreach ($reflectionClass->getMethods() as $method) {
      if ($method->getDeclaringClass()->name !== $className) {
        continue;
      }

      // @todo You may need to use a custom accessor method e.g apiGetFields i.e 'apiGet' or simply 'api'
      $accessorOrMutator = preg_match('/^(api)(.+)$/i', $method->name, $matches);

      if (!$accessorOrMutator) {
        continue;
      }

      $fields = $method->invoke($this->classResolver->getInstanceFromDefinition($className));
      foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
        if ($annotation instanceof Groups) {
          foreach ($fields as $attributeName) {
            if (isset($attributesMetadata[$attributeName])) {
              $attributeMetadata = $attributesMetadata[$attributeName];
            } else {
              $attributesMetadata[$attributeName] = $attributeMetadata = new AttributeMetadata($attributeName);
              $classMetadata->addAttributeMetadata($attributeMetadata);
            }

            foreach ($annotation->getGroups() as $group) {
              $attributeMetadata->addGroup($group);
            }
          }
        }
        $loaded = true;
      }

    }

    return $loaded;
  }

}
