<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Serializer;

/**
 * Creates and manipulates the Serializer context.
 */
trait ContextTrait
{
    /**
     * Initializes the context.
     */
    private function initContext(string $resourceClass, array $context): array
    {
        return array_merge($context, [
            'api_sub_level' => true,
            'resource_class' => $resourceClass,
        ]);
    }
}
