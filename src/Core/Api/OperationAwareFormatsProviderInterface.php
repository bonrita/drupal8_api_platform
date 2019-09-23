<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

/**
 * Extracts formats for a given operation according to the retrieved Metadata.
 *
 */
interface OperationAwareFormatsProviderInterface extends FormatsProviderInterface
{
    /**
     * Finds formats for an operation.
     */
    public function getFormatsFromOperation(string $resourceClass, string $operationName, string $operationType): array;
}
