<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

/**
 * Class FormatMatcher
 *
 * @package Drupal\api_platform\Core\Api
 *
 * @internal
 *
 * Matches a mime type to a format.
 */
final class FormatMatcher {

  /**
   * @var array<string, string[]>
   */
  private $formats;

  /**
   * @param array<string, string[]|string> $formats
   */
  public function __construct(array $formats)
  {
    $normalizedFormats = [];
    foreach ($formats as $format => $mimeTypes) {
      $normalizedFormats[$format] = (array) $mimeTypes;
    }
    $this->formats = $normalizedFormats;
  }

  /**
   * Gets the format associated with the mime type.
   *
   * Adapted from {@see \Symfony\Component\HttpFoundation\Request::getFormat}.
   */
  public function getFormat(string $mimeType): ?string
  {
    $canonicalMimeType = null;
    $pos = strpos($mimeType, ';');
    if (false !== $pos) {
      $canonicalMimeType = trim(substr($mimeType, 0, $pos));
    }

    foreach ($this->formats as $format => $mimeTypes) {
      if (\in_array($mimeType, $mimeTypes, true)) {
        return $format;
      }
      if (null !== $canonicalMimeType && \in_array($canonicalMimeType, $mimeTypes, true)) {
        return $format;
      }
    }

    return null;
  }

}
