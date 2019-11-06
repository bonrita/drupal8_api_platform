<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;


use Drupal\api_platform\Core\Exception\RuntimeException;
use Drupal\api_platform\Core\Util\ClassInfoTrait;
use Drupal\api_platform\Core\Util\ResourceClassInfoTrait;
use Drupal\Core\Cache\CacheBackendInterface;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class CachedIdentifiersExtractor implements IdentifiersExtractorInterface {

  use ClassInfoTrait;

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

    public function __construct(CacheBackendInterface $cache, IdentifiersExtractorInterface $decorated, PropertyAccessorInterface $propertyAccessor = null, ResourceClassResolverInterface $resourceClassResolver = null)
    {
      $this->cacheItemPool = $cache;
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
    $keys = $this->getKeys($item, function ($item) use (&$identifiers) {
      return $identifiers = $this->decorated->getIdentifiersFromItem($item);
    });

    if (null !== $identifiers) {
      return $identifiers;
    }

    $identifiers = [];
    foreach ($keys as $propertyName) {
      if ($item->get($propertyName)->isEmpty()) {
        $identifiers[$propertyName] = '';
      } else {
        $fieldDefinition =  $item->getFieldDefinition($propertyName);
        $identifiers[$propertyName] = $item->get($propertyName)->getValue()[0][$fieldDefinition->getMainPropertyName()];
      }

//      $identifiers[$propertyName] = $item->get($propertyName)->getValue();

      if (!\is_object($identifiers[$propertyName])) {
        continue;
      }

      $this->executeException();
    }

    return $identifiers;
  }

  private function getKeys($item, callable $retriever): array
  {
    $resourceClass = $this->getObjectClass($item);
    if (isset($this->localCache[$resourceClass])) {
      return $this->localCache[$resourceClass];
    }

    $cid = self::CACHE_KEY_PREFIX.md5($resourceClass);

    if ($cache = $this->cacheItemPool->get($cid)) {
      $this->localCache[$resourceClass] = $cache->data;
    } else {

      $identifiers = $retriever($item);

      $this->localCache[$resourceClass] =  $identifiers;
      $this->cacheItemPool->set($cid, $identifiers);
    }

    return $this->localCache[$resourceClass];
  }

}
