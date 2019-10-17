<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Action;

/**
 * Placeholder returning the data passed in parameter.
 */
final class PlaceholderAction
{
    /**
     * @param object $data
     *
     * @return object
     */
    public function __invoke($data)
    {
        return $data;
    }
}
