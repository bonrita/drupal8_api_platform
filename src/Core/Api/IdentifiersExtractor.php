<?php
declare(strict_types=1);

namespace Drupal\api_platform\Core\DataProvider;

namespace Drupal\api_platform\Core\Api;


use Drupal\api_platform\Core\Exception\RuntimeException;
use Drupal\api_platform\Core\Metadata\Extractor\EntityExtractor;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use Drupal\api_platform\Core\Util\ResourceClassInfoTrait;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class IdentifiersExtractor implements IdentifiersExtractorInterface {

  use ResourceClassInfoTrait;

  private $propertyNameCollectionFactory;
  private $propertyMetadataFactory;
  private $propertyAccessor;

  /**
   * @var \Drupal\api_platform\Core\Metadata\Extractor\EntityExtractor
   */
  private $entityExtractor;

  public function __construct(PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory, PropertyMetadataFactoryInterface $propertyMetadataFactory, EntityExtractor $entityExtractor, PropertyAccessorInterface $propertyAccessor = null, ResourceClassResolverInterface $resourceClassResolver = null)
  {
    $this->propertyNameCollectionFactory = $propertyNameCollectionFactory;
    $this->propertyMetadataFactory = $propertyMetadataFactory;
    $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
    $this->resourceClassResolver = $resourceClassResolver;
    $this->entityExtractor = $entityExtractor;

    if (null === $this->resourceClassResolver) {
      @trigger_error(sprintf('Not injecting %s in the IdentifiersExtractor might introduce cache issues with object identifiers.', ResourceClassResolverInterface::class), E_USER_DEPRECATED);
    }
  }


  /**
   * @inheritDoc
   */
  public function getIdentifiersFromResourceClass(string $resourceClass
  ): array {
    $identifiers = [];

    if ($id = $this->entityExtractor->getIndentifier($resourceClass)) {
      $identifiers[] = $id;
    }

    return $identifiers;
  }

  /**
   * @inheritDoc
   */
  public function getIdentifiersFromItem($item): array {
    $identifiers = [];
    $resourceClass = $this->getResourceClass($item, true);

    if ($id = $this->entityExtractor->getIndentifier($resourceClass)) {
      $identifiers[] = $id;
    }

    return $identifiers;
  }

}
