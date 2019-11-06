<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Util;

use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\api_platform\Core\Exception\ResourceClassNotFoundException;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;

/**
 * Retrieves information about a resource class.
 *
 * @internal
 */
trait ResourceClassInfoTrait
{
    use ClassInfoTrait;

    /**
     * @var ResourceClassResolverInterface|null
     */
    private $resourceClassResolver;

    /**
     * @var ResourceMetadataFactoryInterface|null
     */
    private $resourceMetadataFactory;

    /**
     * Gets the resource class of the given object.
     *
     * @param object $object
     * @param bool   $strict If true, object class is expected to be a resource class
     *
     * @return string|null The resource class, or null if object class is not a resource class
     */
    private function getResourceClass($object, bool $strict = false): ?string
    {
        $objectClass = $this->getObjectClass($object);

        if (null === $this->resourceClassResolver) {
            return $objectClass;
        }

        if (!$strict && !$this->resourceClassResolver->isResourceClass($objectClass)) {
            return null;
        }

        // BONA: Changed and passed in the $objectClass.
        return $this->resourceClassResolver->getResourceClass($object, $objectClass);
    }

    private function isResourceClass(string $class): bool
    {
        if ($this->resourceClassResolver instanceof ResourceClassResolverInterface) {
            return $this->resourceClassResolver->isResourceClass($class);
        }

        if (!$this->resourceMetadataFactory instanceof ResourceMetadataFactoryInterface) {
            // assume that it's a resource class
            return true;
        }

        try {
            $this->resourceMetadataFactory->create($class);
        } catch (ResourceClassNotFoundException $e) {
            return false;
        }

        return true;
    }
}
