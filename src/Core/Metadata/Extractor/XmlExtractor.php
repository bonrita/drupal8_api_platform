<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Extractor;

use Drupal\api_platform\Core\Exception\InvalidArgumentException;

/**
 * Class XmlExtractor
 *
 * @package Drupal\api_platform\Core\Metadata\Extractor
 *
 * Extracts an array of metadata from a list of XML files.
 */
class XmlExtractor extends AbstractExtractor {

  public const RESOURCE_SCHEMA = __DIR__.'/../schema/metadata.xsd';

  /**
   * @inheritDoc
   */
  protected function extractPath(string $path) {
    // TODO: Implement extractPath() method.
  }


}
