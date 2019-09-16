<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Util;

/**
 * Class AttributesExtractor
 *
 * @package ApiPlatform\Core\Util
 *
 * Extracts data used by the library form given attributes.
 */
final class AttributesExtractor {

  private function __construct()
  {
  }

  /**
   * Extracts resource class, operation name and format request attributes. Returns an empty array if the request does
   * not contain required attributes.
   */
  public static function extractAttributes(array $attributes): array
  {
    $result = ['resource_class' => $attributes['_api_resource_class'] ?? null];

    if (null === $result['resource_class']) {
      return [];
    }

    return $result;
  }

}
