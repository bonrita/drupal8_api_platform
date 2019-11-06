<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\DataProvider;

/**
 * Restricts a data provider based on a condition.
 */
interface RestrictedDataProviderInterface
{
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool;
}
