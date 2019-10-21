<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

use Psr\Container\ContainerInterface;

/**
 * A list of filters.
 *
 * @deprecated since version 2.1, to be removed in 3.0. Use a service locator {@see \Psr\Container\ContainerInterface}.
 */
final class FilterCollection extends \ArrayObject
{
    public function __construct($input = [], $flags = 0, $iterator_class = 'ArrayIterator')
    {
        @trigger_error(sprintf('The %s class is deprecated since version 2.1 and will be removed in 3.0. Provide an implementation of %s instead.', self::class, ContainerInterface::class), E_USER_DEPRECATED);

        parent::__construct($input, $flags, $iterator_class);
    }
}
