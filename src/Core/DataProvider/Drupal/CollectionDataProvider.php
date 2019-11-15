<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\DataProvider\Drupal;


use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\api_platform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use Drupal\api_platform\Core\DataProvider\RestrictedDataProviderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class CollectionDataProvider implements RestrictedDataProviderInterface, ContextAwareCollectionDataProviderInterface {

  /**
   * @var \Drupal\api_platform\Core\Api\ResourceClassResolverInterface
   */
  private $resourceClassResolver;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * @var string
   */
  private $entityTypeId;

  public function __construct(ResourceClassResolverInterface $resourceClassResolver, EntityTypeManagerInterface $entityTypeManager) {

    $this->resourceClassResolver = $resourceClassResolver;
    $this->entityTypeManager = $entityTypeManager;
  }

  public function supports(
    string $resourceClass,
    string $operationName = NULL,
    array $context = []
  ): bool {
    $this->entityTypeId = $this->resourceClassResolver->getEntityTypeId($resourceClass);
    return !empty($this->entityTypeId);
  }

  /**
   * @inheritDoc
   */
  public function getCollection(
    string $resourceClass,
    string $operationName = null,
    array $context = []
  ) {
    $entities = [];
    $storage = $this->entityTypeManager->getStorage($this->entityTypeId);

    /** @var \Drupal\Core\Entity\Query\Sql\Query $query */
    $query = $storage->getQuery();

    $bundleKey = $this->resourceClassResolver->getBundleKey($resourceClass);
    if (!empty($bundleKey) && isset($context['filters'][$bundleKey]) && !empty($context['filters'][$bundleKey])) {
      $query->condition($bundleKey, $context['filters'][$bundleKey]);
    }

    $entityIds = $query->execute();

    if (!empty($entityIds)) {
      $entities = $storage->loadMultiple($entityIds);
    }

    return $entities;
  }

}
