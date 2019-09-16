<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

/**
 * Interface FormatsProviderInterface
 *
 * @package ApiPlatform\Core\Api
 *
 * Extracts formats for a given operation according to the retrieved Metadata.
 */
interface FormatsProviderInterface {
  /**
   * Finds formats for an operation.
   */
  public function getFormatsFromAttributes(array $attributes): array;

}
