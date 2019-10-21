<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Resource;

use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;

/**
 * @internal
 */
trait ToggleableOperationAttributeTrait
{
    /**
     * @var ResourceMetadataFactoryInterface|null
     */
    private $resourceMetadataFactory;

    private function isOperationAttributeDisabled(array $attributes, string $attribute, bool $default = false, bool $resourceFallback = true): bool
    {
return FALSE;
    }
}
