<?php


namespace Drupal\api_platform\Core\Metadata\Property\Factory;


use Doctrine\Common\Annotations\Reader;
use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\api_platform\Core\Exception\PropertyNotFoundException;
use Drupal\api_platform\Core\Metadata\Property\PropertyMetadata;
use Drupal\Core\DependencyInjection\ClassResolverInterface;

class NonEntityFieldMetadataFactory implements PropertyMetadataFactoryInterface {

  private $decorated;

  /**
   * @var \Drupal\api_platform\Core\Api\ResourceClassResolverInterface
   */
  private $resourceClassResolver;

  /**
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  private $classResolver;

  public function __construct(PropertyMetadataFactoryInterface $decorated, ResourceClassResolverInterface $resourceClassResolver, ClassResolverInterface $classResolver)
  {
    $this->decorated = $decorated;
    $this->resourceClassResolver = $resourceClassResolver;
    $this->classResolver = $classResolver;
  }

  /**
   * @inheritDoc
   */
  public function create(
    string $resourceClass,
    string $property,
    array $options = []
  ): PropertyMetadata {
    $propertyMetadata = NULL;
    if ($this->decorated) {
      try {
        $propertyMetadata = $this->decorated->create($resourceClass, $property, $options);
      } catch (PropertyNotFoundException $propertyNotFoundException) {
        // Ignore not found exception from decorated factories
      }
    }

    return $propertyMetadata;
  }

}
