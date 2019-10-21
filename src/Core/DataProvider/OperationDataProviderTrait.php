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
     * @param array $parameters - usually comes from $request->attributes->all()
     *
     * @throws InvalidIdentifierException
     */
    private function extractIdentifiers(array $parameters, array $attributes)
    {
        if (isset($attributes['item_operation_name'])) {
            if (!isset($parameters['id'])) {
                throw new InvalidIdentifierException('Parameter "id" not found');
            }

            $id = $parameters['id'];

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
}
