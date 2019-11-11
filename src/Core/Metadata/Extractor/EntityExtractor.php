<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Extractor;


use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Exception\NoCorrespondingEntityClassException;

class EntityExtractor {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * @var \Drupal\api_platform\Core\Api\ResourceClassResolverInterface
   */
  private $resourceClassResolver;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, ResourceClassResolverInterface $resourceClassResolver)
  {
    $this->entityTypeManager = $entityTypeManager;
    $this->resourceClassResolver = $resourceClassResolver;
  }

  public function getIndentifier(string $resourceClass): string {
    try {
      $entityTypeID = $this->resourceClassResolver->getEntityTypeId($resourceClass);
      $identifier = $this->entityTypeManager->getDefinition($entityTypeID)->getKey('id');
    } catch (NoCorrespondingEntityClassException $e) {
      $identifier = NULL;
    }

    return $identifier;
  }

}
