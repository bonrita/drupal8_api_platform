<?php


namespace Drupal\api_platform\Core\DataProvider\Drupal;


use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\api_platform\Core\DataProvider\ItemDataProviderInterface;
use Drupal\api_platform\Core\DataProvider\RestrictedDataProviderInterface;
use Drupal\api_platform\Core\Exception\ResourceClassNotSupportedException;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Entity\Exception\NoCorrespondingEntityClassException;

class ItemDataProvider implements RestrictedDataProviderInterface, ItemDataProviderInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * @var string
   */
  private $entityTypeId;

  /**
   * @var \Drupal\api_platform\Core\Api\ResourceClassResolverInterface
   */
  private $resourceClassResolver;


  public function __construct(
    ResourceClassResolverInterface $resourceClassResolver,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->resourceClassResolver = $resourceClassResolver;
  }

  public function supports(
    string $resourceClass,
    string $operationName = NULL,
    array $context = []
  ): bool {
    try {
      if(NULL !== $this->entityTypeId = $this->resourceClassResolver->getEntityTypeId($resourceClass)) {
        return TRUE;
      }
    } catch (NoCorrespondingEntityClassException $e) {
      return FALSE;
    }

    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function getItem(
    string $resourceClass,
    $id,
    string $operationName = NULL,
    array $context = []
  ) {
    $id = $id[$this->resourceClassResolver->getIdKey($resourceClass)];
    return $this->entityTypeManager->getStorage($this->entityTypeId)->load($id);
  }

}
