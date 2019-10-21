<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

use Drupal\api_platform\Core\Exception\RuntimeException;

/**
 * Extracts identifiers for a given Resource according to the retrieved Metadata.
 */
interface IdentifiersExtractorInterface
{
    /**
     * Finds identifiers from a Resource class.
     */
    public function getIdentifiersFromResourceClass(string $resourceClass): array;

    /**
     * Finds identifiers from an Item (object).
     *
     * @param object $item
     *
     * @throws RuntimeException
     */
    public function getIdentifiersFromItem($item): array;
}
