<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Extractor;

use Drupal\api_platform\Core\Exception\InvalidArgumentException;

/**
 * Interface ExtractorInterface
 *
 * @package Drupal\api_platform\Core\Metadata\Extractor
 *
 * Extracts an array of metadata from a file or a list of files.
 */
interface ExtractorInterface {

  /**
   * Parses all metadata files and convert them in an array.
   *
   * @throws InvalidArgumentException
   */
  public function getResources(): array;

}
