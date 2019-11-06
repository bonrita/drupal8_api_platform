<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Util;

/**
 * Retrieves information about a class.
 *
 * @internal
 */
trait ClassInfoTrait
{
    /**
     * Get class name of the given object.
     *
     * @param object $object
     */
    private function getObjectClass($object): string
    {
        return $this->getRealClassName(\get_class($object));
    }

    /**
     * Get the real class name of a class name that could be a proxy.
     */
    private function getRealClassName(string $className): string
    {
        // __CG__: Doctrine Common Marker for Proxy (ODM < 2.0 and ORM < 3.0)
        // __PM__: Ocramius Proxy Manager (ODM >= 2.0)
        if ((false === $positionCg = strrpos($className, '\\__CG__\\')) &&
            (false === $positionPm = strrpos($className, '\\__PM__\\'))) {
            return $className;
        }

        if (false !== $positionCg) {
            return substr($className, $positionCg + 8);
        }

        $className = ltrim($className, '\\');

        return substr(
            $className,
            8 + $positionPm,
            strrpos($className, '\\') - ($positionPm + 8)
        );
    }
}
