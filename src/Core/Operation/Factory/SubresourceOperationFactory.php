<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Operation\Factory;

use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Operation\PathSegmentNameGeneratorInterface;

class SubresourceOperationFactory  implements SubresourceOperationFactoryInterface {

  private $resourceMetadataFactory;
  private $propertyNameCollectionFactory;
  private $propertyMetadataFactory;
  private $pathSegmentNameGenerator;

  public function __construct(ResourceMetadataFactoryInterface $resourceMetadataFactory, PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory, PropertyMetadataFactoryInterface $propertyMetadataFactory, PathSegmentNameGeneratorInterface $pathSegmentNameGenerator)
  {
    $this->resourceMetadataFactory = $resourceMetadataFactory;
    $this->propertyNameCollectionFactory = $propertyNameCollectionFactory;
    $this->propertyMetadataFactory = $propertyMetadataFactory;
    $this->pathSegmentNameGenerator = $pathSegmentNameGenerator;
  }

  /**
   * @inheritDoc
   */
  public function create(string $resourceClass, array $options = []): array {
    $tree = [];
    $this->computeSubresourceOperations($resourceClass, $tree, NULL, NULL, [], 0, NULL, $options);

    return $tree;
  }

  private function computeSubresourceOperations(
    string $resourceClass,
    array &$tree,
    string $rootResourceClass = null,
    array $parentOperation = null,
    array $visited = [],
    int $depth = 0,
    int $maxDepth = null,
    array $options = []
  ): void {
    if (null === $rootResourceClass) {
      $rootResourceClass = $resourceClass;
    }

    foreach ($this->propertyNameCollectionFactory->create($resourceClass, $options) as $property) {
      $propertyMetadata = $this->propertyMetadataFactory->create($resourceClass, $property, $options);

      if (!$subresource = $propertyMetadata->getSubresource()) {
        continue;
      }

      $gg = 0;
    }

    $gg =0;
  }

}
