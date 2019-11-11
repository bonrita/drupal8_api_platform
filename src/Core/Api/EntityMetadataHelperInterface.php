<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;


interface EntityMetadataHelperInterface {

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

}
