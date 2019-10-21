<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\DataProvider;

use Drupal\api_platform\Core\Exception\ResourceClassNotSupportedException;

/**
 * Retrieves items from a persistence layer.
 */
interface ItemDataProviderInterface
{
    /**
     * Retrieves an item.
     *
     * @param array|int|string $id
     *
     * @throws ResourceClassNotSupportedException
     *
     * @return object|null
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []);
}
