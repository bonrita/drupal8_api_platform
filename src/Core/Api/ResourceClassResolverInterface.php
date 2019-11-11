<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

use Drupal\api_platform\Core\Exception\InvalidArgumentException;

/**
 * Guesses which resource is associated with a given object.
 *
 */
interface ResourceClassResolverInterface
{
    /**
     * Guesses the associated resource.
     *
     * @param string $resourceClass The expected resource class
     * @param bool   $strict        If true, value must match the expected resource class
     *
     * @throws InvalidArgumentException
     */
    public function getResourceClass($value, string $resourceClass = null, bool $strict = false): string;

    /**
     * Is the given class a resource class?
     */
    public function isResourceClass(string $type): bool;

  /**
   * Get entity bundles.
   *
   * @param string $resourceClass
   *
   * @return array
   */
  public function getBundles(string $resourceClass): array;

  /**
   * Get entity id key.
   *
   * @param string $resourceClass
   *
   * @return string
   */
  public function getIdKey(string $resourceClass): string;

  /**
   * Get entity type Id.
   *
   * @param string $resourceClass
   *   The resource class.
   *
   * @return string
   *   The entity type id.
   */
  public function getEntityTypeId(string $resourceClass): string;

  /**
   * Get bundle key.
   *
   * @param string $resourceClass
   *   The resource class.
   *
   * @return string
   *   Bundle key.
   */
  public function getBundleKey(string $resourceClass): string;

  /**
   * Get the entity class from the wrapper.
   *
   * @param string $resourceClass
   *
   * @return string|null
   *   The entity class.
   */
  public function getActualResourceClass(string $resourceClass): ?string;

}
