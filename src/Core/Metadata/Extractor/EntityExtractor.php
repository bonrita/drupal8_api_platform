<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Extractor;


use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Entity\Exception\NoCorrespondingEntityClassException;

class EntityExtractor {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  private $entityTypeRepository;

  public function __construct(EntityTypeRepositoryInterface $entityTypeRepository, EntityTypeManagerInterface $entityTypeManager)
  {
    $this->entityTypeRepository = $entityTypeRepository;
    $this->entityTypeManager = $entityTypeManager;
  }

  public function getIndentifier(string $resourceClass): string {
    try {
      $entityTypeID = $this->entityTypeRepository->getEntityTypeFromClass($resourceClass);
      $identifier = $this->entityTypeManager->getDefinition($entityTypeID)->getKey('id');
    } catch (NoCorrespondingEntityClassException $e) {
      $identifier = NULL;
    }

    return $identifier;
  }

}
