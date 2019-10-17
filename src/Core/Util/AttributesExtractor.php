<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Util;

use Drupal\api_platform\Core\Api\OperationType;

/**
 * Class AttributesExtractor
 *
 * @package Drupalapi_platform\Core\Util
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
    if ($subresourceContext = $attributes['_api_subresource_context'] ?? null) {
      $result['subresource_context'] = $subresourceContext;
    }

    if (null === $result['resource_class']) {
      return [];
    }

    $hasRequestAttributeKey = false;
    foreach (OperationType::TYPES as $operationType) {
      $attribute = "_api_{$operationType}_operation_name";
      if (isset($attributes[$attribute])) {
        $result["{$operationType}_operation_name"] = $attributes[$attribute];
        $hasRequestAttributeKey = true;
        break;
      }
    }

    if (false === $hasRequestAttributeKey) {
      return [];
    }

    $result += [
      'receive' => (bool) ($attributes['_api_receive'] ?? true),
      'respond' => (bool) ($attributes['_api_respond'] ?? true),
      'persist' => (bool) ($attributes['_api_persist'] ?? true),
    ];

    return $result;
  }

}
