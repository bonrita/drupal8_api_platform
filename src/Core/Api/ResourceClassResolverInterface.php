<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

use Drupal\api_platform\Core\Exception\InvalidArgumentException;

/**
 * Guesses which resource is associated with a given object.
 *
 */
interface ResourceClassResolverInterface
{
    /**
     * Guesses the associated resource.
     *
     * @param string $resourceClass The expected resource class
     * @param bool   $strict        If true, value must match the expected resource class
     *
     * @throws InvalidArgumentException
     */
    public function getResourceClass($value, string $resourceClass = null, bool $strict = false): string;

    /**
     * Is the given class a resource class?
     */
    public function isResourceClass(string $type): bool;
}
