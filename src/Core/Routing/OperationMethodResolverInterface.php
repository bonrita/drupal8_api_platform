<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Routing;

use Drupal\api_platform\Core\Api\OperationMethodResolverInterface as BaseOperationMethodResolverInterface;
use Drupal\api_platform\Core\Exception\RuntimeException;
use Symfony\Component\Routing\Route;

/**
 * Resolves the HTTP method associated with an operation, extended for Symfony routing.
 */
interface OperationMethodResolverInterface extends BaseOperationMethodResolverInterface
{
    /**
     * @throws RuntimeException
     */
    public function getCollectionOperationRoute(string $resourceClass, string $operationName): Route;

    /**
     * @throws RuntimeException
     */
    public function getItemOperationRoute(string $resourceClass, string $operationName): Route;
}
