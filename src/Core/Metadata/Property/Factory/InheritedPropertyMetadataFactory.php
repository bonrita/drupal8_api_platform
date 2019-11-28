<?php


namespace Drupal\api_platform\Core\Metadata\Property\Factory;


use Drupal\api_platform\Core\Exception\PropertyNotFoundException;
use Drupal\api_platform\Core\Metadata\Property\PropertyMetadata;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;

class InheritedPropertyMetadataFactory implements PropertyMetadataFactoryInterface {

  private $resourceNameCollectionFactory;
  private $decorated;

  public function __construct(ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory, PropertyMetadataFactoryInterface $decorated = null)
  {
    $this->resourceNameCollectionFactory = $resourceNameCollectionFactory;
    $this->decorated = $decorated;
  }

  /**
   * @inheritDoc
   */
  public function create(
    string $resourceClass,
    string $property,
    array $options = []
  ): PropertyMetadata {
    $propertyMetadata = $this->decorated ? $this->decorated->create($resourceClass, $property, $options) : new PropertyMetadata();

    foreach ($this->resourceNameCollectionFactory->create() as $knownResourceClass) {
      if ($resourceClass === $knownResourceClass) {
        continue;
      }

      if (is_subclass_of($knownResourceClass, $resourceClass)) {
        $propertyMetadata = $this->create($knownResourceClass, $property, $options);

        return $propertyMetadata->withChildInherited($knownResourceClass);
      }
    }

    return $propertyMetadata;
  }

}
