<?php
declare(strict_types=1);

namespace Drupal\api_platform\Core\Util;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestAttributesExtractor
 *
 * Extracts data used by the library form a Request instance.
 */
final class RequestAttributesExtractor {

  private function __construct()
  {
  }

  /**
   * Extracts resource class, operation name and format request attributes. Returns an empty array if the request does
   * not contain required attributes.
   */
  public static function extractAttributes(Request $request): array
  {
    return AttributesExtractor::extractAttributes($request->attributes->all());
  }

}
