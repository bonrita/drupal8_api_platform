<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\DataProvider;

use Drupal\api_platform\Core\Exception\ResourceClassNotSupportedException;

/**
 * Retrieves subresources from a persistence layer.
 */
interface SubresourceDataProviderInterface
{
    /**
     * Retrieves a subresource of an item.
     *
     * @param string $resourceClass The root resource class
     * @param array  $identifiers   Identifiers and their values
     * @param array  $context       The context indicates the conjunction between collection properties (identifiers) and their class
     * @param string $operationName
     *
     * @throws ResourceClassNotSupportedException
     *
     * @return iterable|object|null
     */
    public function getSubresource(string $resourceClass, array $identifiers, array $context, string $operationName = null);
}
