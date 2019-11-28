<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Operation\Factory;

/**
 * Interface SubresourceOperationFactoryInterface
 *
 * @package ApiPlatform\Core\Operation\Factory
 *
 * Computes subresource operation for a given resource.
 */
interface SubresourceOperationFactoryInterface {

  /**
   * Creates subresource operations.
   */
  public function create(string $resourceClass, array $options = []): array;

}
