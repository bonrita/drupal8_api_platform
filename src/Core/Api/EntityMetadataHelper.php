<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;


use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Entity\Exception\NoCorrespondingEntityClassException;

class EntityMetadataHelper implements EntityMetadataHelperInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  private $entityTypeRepository;

  /**
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  private $entityTypeBundleInfo;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * @var \Drupal\api_platform\Core\Api\ResourceClassResolverInterface
   */
  private $resourceClassResolver;

  public function __construct(
    EntityTypeRepositoryInterface $entityTypeRepository,
    EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    EntityTypeManagerInterface $entityTypeManager,
    ResourceClassResolverInterface $resourceClassResolver
  )
  {
    $this->entityTypeRepository = $entityTypeRepository;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
    $this->entityTypeManager = $entityTypeManager;
    $this->resourceClassResolver = $resourceClassResolver;
  }

  /**
   * {@inheritDoc}
   */
  public function getBundles(string $resourceClass): array {
    $entityTypeId = $this->getEntityTypeId($resourceClass);
    return $this->entityTypeBundleInfo->getBundleInfo($entityTypeId);
  }

  /**
   * {@inheritDoc}
   */
  public function getIdKey(string $resourceClass): string {
    $entityTypeId = $this->getEntityTypeId($resourceClass);
    $definition = $this->entityTypeManager->getDefinition($entityTypeId);
    return $definition->getKey('id');
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityTypeId(string $resourceClass): string {
    try{
      $entityTypeId = $this->entityTypeRepository->getEntityTypeFromClass($resourceClass);
    } catch (NoCorrespondingEntityClassException $e) {
      $wrappedClass = $this->resourceClassResolver->getActualResourceClass($resourceClass);
      $entityTypeId = $this->entityTypeRepository->getEntityTypeFromClass($wrappedClass);
    }
    return $entityTypeId;
  }

  /**
   * {@inheritDoc}
   */
  public function getBundleKey(string $resourceClass): string {
    $entityTypeId = $this->getEntityTypeId($resourceClass);
    $definition = $this->entityTypeManager->getDefinition($entityTypeId);
    return $definition->getKey('bundle');
  }

}
