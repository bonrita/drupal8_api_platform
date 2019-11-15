<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\DataProvider;

use Drupal\api_platform\Core\Exception\InvalidIdentifierException;
use Drupal\api_platform\Core\Exception\RuntimeException;
use Drupal\api_platform\Core\Identifier\IdentifierConverterInterface;

/**
 * @internal
 */
trait OperationDataProviderTrait
{
    /**
     * @var CollectionDataProviderInterface
     */
    private $collectionDataProvider;

    /**
     * @var ItemDataProviderInterface
     */
    private $itemDataProvider;

    /**
     * @var SubresourceDataProviderInterface|null
     */
    private $subresourceDataProvider;

    /**
     * @var IdentifierConverterInterface|null
     */
    private $identifierConverter;

    /**
     * @var \Drupal\api_platform\Core\Api\ResourceClassResolverInterface
     */
    private $resourceClassResolver;

    /**
     * @param array $parameters - usually comes from $request->attributes->all()
     *
     * @throws InvalidIdentifierException
     */
    private function extractIdentifiers(array $parameters, array $attributes)
    {
        if (isset($attributes['item_operation_name'])) {
          $idKey = $this->resourceClassResolver->getIdKey($attributes["resource_class"]);
//          if (!isset($parameters['id'])) {
//              throw new InvalidIdentifierException('Parameter "id" not found');
//          }
//
//          $id = $parameters['id'];

          if (!isset($parameters[$idKey])) {
            throw new InvalidIdentifierException('Parameter "id" not found');
          }

          $id = $parameters[$idKey];

          if (null !== $this->identifierConverter) {
              return $this->identifierConverter->convert((string) $id, $attributes['resource_class']);
          }

        }

    }

  /**
   * Gets data for an item operation.
   *
   * @return object|null
   */
  private function getItemData($identifiers, array $attributes, array $context)
  {
    return $this->itemDataProvider->getItem($attributes['resource_class'], $identifiers, $attributes['item_operation_name'], $context);
  }

  /**
   * Retrieves data for a collection operation.
   *
   * @return iterable|null
   */
  private function getCollectionData(array $attributes, array $context)
  {
    return $this->collectionDataProvider->getCollection($attributes['resource_class'], $attributes['collection_operation_name'], $context);
  }

}
