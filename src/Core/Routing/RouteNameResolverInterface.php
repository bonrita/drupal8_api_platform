<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Routing;

use Drupal\api_platform\Core\Exception\InvalidArgumentException;

/**
 * Resolves the Symfony route name associated with a resource.
 */
interface RouteNameResolverInterface
{
    /**
     * Finds the route name for a resource.
     *
     * @param bool|string $operationType
     *
     * @throws InvalidArgumentException
     */
    public function getRouteName(string $resourceClass, $operationType /*, array $context = [] */): string;
}
