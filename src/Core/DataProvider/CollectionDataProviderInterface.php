<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\DataProvider;

use Drupal\api_platform\Core\Exception\ResourceClassNotSupportedException;

/**
 * Retrieves items from a persistence layer.
 */
interface CollectionDataProviderInterface
{
    /**
     * Retrieves a collection.
     *
     * @throws ResourceClassNotSupportedException
     *
     * @return iterable
     */
    public function getCollection(string $resourceClass, string $operationName = null);
}
