<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Drupal\api_platform\Core\Routing;

use Drupal\api_platform\Core\Api\OperationMethodResolverInterface as BaseOperationMethodResolverInterface;
use Drupal\api_platform\Core\Exception\RuntimeException;
use Symfony\Component\Routing\Route;

/**
 * Resolves the HTTP method associated with an operation, extended for Symfony routing.
 *
 * @author Teoh Han Hui <teohhanhui@gmail.com>
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
