<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\DataProvider;

/**
 * Retrieves items from a persistence layer and allow to pass a context to it.
 */
interface ContextAwareCollectionDataProviderInterface extends CollectionDataProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []);
}
