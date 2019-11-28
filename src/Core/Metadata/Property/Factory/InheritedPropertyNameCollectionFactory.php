<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Property\Factory;


use Drupal\api_platform\Core\Exception\ResourceClassNotFoundException;
use Drupal\api_platform\Core\Metadata\Property\PropertyNameCollection;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;

final class InheritedPropertyNameCollectionFactory implements PropertyNameCollectionFactoryInterface {

  private $resourceNameCollectionFactory;
  private $decorated;

  public function __construct(ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory, PropertyNameCollectionFactoryInterface $decorated = null)
  {
    $this->resourceNameCollectionFactory = $resourceNameCollectionFactory;
    $this->decorated = $decorated;
  }

  /**
   * @inheritDoc
   */
  public function create(
    string $resourceClass,
    array $options = []
  ): PropertyNameCollection {
    $propertyNames = [];

    // Inherited from parent
    if ($this->decorated) {
      foreach ($this->decorated->create($resourceClass, $options) as $propertyName) {
        $propertyNames[$propertyName] = (string) $propertyName;
      }
    }

    foreach ($this->resourceNameCollectionFactory->create() as $knownResourceClass) {
      if ($resourceClass === $knownResourceClass) {
        continue;
      }
      $this->boy();
    }

    return new PropertyNameCollection(array_values($propertyNames));
  }

}
