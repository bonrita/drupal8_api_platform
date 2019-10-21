<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Routing;

use Drupal\api_platform\Core\Api\OperationType;
use Drupal\api_platform\Core\Api\OperationTypeDeprecationHelper;
use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Doctrine\Common\Inflector\Inflector;

/**
 * Generates the Symfony route name associated with an operation name and a resource short name.
 *
 * @internal
 */
final class RouteNameGenerator
{
    public const ROUTE_NAME_PREFIX = 'api_';

    private function __construct()
    {
    }

    /**
     * Generates a Symfony route name.
     *
     * @param string|bool $operationType
     *
     * @throws InvalidArgumentException
     */
    public static function generate(string $operationName, string $resourceShortName, $operationType): string
    {
        if (OperationType::SUBRESOURCE === $operationType = OperationTypeDeprecationHelper::getOperationType($operationType)) {
            throw new InvalidArgumentException('Subresource operations are not supported by the RouteNameGenerator.');
        }

        return sprintf(
            '%s%s_%s_%s',
            static::ROUTE_NAME_PREFIX,
            self::inflector($resourceShortName),
            $operationName,
            $operationType
        );
    }

    /**
     * Transforms a given string to a tableized, pluralized string.
     *
     * @param string $name usually a ResourceMetadata shortname
     *
     * @return string A string that is a part of the route name
     */
    public static function inflector(string $name, bool $pluralize = true): string
    {
        $name = Inflector::tableize($name);

        return $pluralize ? Inflector::pluralize($name) : $name;
    }
}
