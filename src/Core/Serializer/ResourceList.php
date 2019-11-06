<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Serializer;

/**
 * @internal
 */
class ResourceList extends \ArrayObject
{
    /**
     * {@inheritdoc}
     */
    public function serialize(): ?string
    {
        return null;
    }
}
