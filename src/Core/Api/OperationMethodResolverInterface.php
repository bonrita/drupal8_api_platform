<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

use Drupal\api_platform\Core\Exception\RuntimeException;

/**
 * Resolves the uppercased HTTP method associated with an operation.
 *
 */
interface OperationMethodResolverInterface
{
    /**
     * Resolves the uppercased HTTP method associated with a collection operation.
     *
     * @throws RuntimeException
     */
    public function getCollectionOperationMethod(string $resourceClass, string $operationName): string;

    /**
     * Resolves the uppercased HTTP method associated with an item operation.
     *
     * @throws RuntimeException
     */
    public function getItemOperationMethod(string $resourceClass, string $operationName): string;
}
