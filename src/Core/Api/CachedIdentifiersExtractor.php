<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;


use Drupal\api_platform\Core\Exception\RuntimeException;
use Drupal\api_platform\Core\Util\ResourceClassInfoTrait;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class CachedIdentifiersExtractor implements IdentifiersExtractorInterface {

  public const CACHE_KEY_PREFIX = 'iri_identifiers';

  private $cacheItemPool;
  private $propertyAccessor;
  private $decorated;
  private $localCache = [];
  private $localResourceCache = [];

//  public function __construct(CacheItemPoolInterface $cacheItemPool, IdentifiersExtractorInterface $decorated, PropertyAccessorInterface $propertyAccessor = null, ResourceClassResolverInterface $resourceClassResolver = null)
//  {
//    $this->cacheItemPool = $cacheItemPool;
//    $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
//    $this->decorated = $decorated;
//    $this->resourceClassResolver = $resourceClassResolver;
//
//    if (null === $this->resourceClassResolver) {
//      @trigger_error(sprintf('Not injecting %s in the CachedIdentifiersExtractor might introduce cache issues with object identifiers.', ResourceClassResolverInterface::class), E_USER_DEPRECATED);
//    }
//  }

    public function __construct(IdentifiersExtractorInterface $decorated, PropertyAccessorInterface $propertyAccessor = null, ResourceClassResolverInterface $resourceClassResolver = null)
    {
      $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
      $this->decorated = $decorated;
//      $this->resourceClassResolver = $resourceClassResolver;
//
//      if (null === $this->resourceClassResolver) {
//        @trigger_error(sprintf('Not injecting %s in the CachedIdentifiersExtractor might introduce cache issues with object identifiers.', ResourceClassResolverInterface::class), E_USER_DEPRECATED);
//      }
    }


//  public function __construct(PropertyAccessorInterface $propertyAccessor = null, ResourceClassResolverInterface $resourceClassResolver = null)
//  {
//    $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
//    $this->resourceClassResolver = $resourceClassResolver;
//
//    if (null === $this->resourceClassResolver) {
//      @trigger_error(sprintf('Not injecting %s in the CachedIdentifiersExtractor might introduce cache issues with object identifiers.', ResourceClassResolverInterface::class), E_USER_DEPRECATED);
//    }
//  }


  /**
   * {@inheritdoc}
   */
  public function getIdentifiersFromResourceClass(string $resourceClass): array
  {
    if (isset($this->localResourceCache[$resourceClass])) {
      return $this->localResourceCache[$resourceClass];
    }

    return $this->localResourceCache[$resourceClass] = $this->decorated->getIdentifiersFromResourceClass($resourceClass);
  }

  /**
   * @inheritDoc
   */
  public function getIdentifiersFromItem($item): array {
    // TODO: Implement getIdentifiersFromItem() method.
  }


}
