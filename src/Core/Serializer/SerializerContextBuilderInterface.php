<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Serializer;

use Drupal\api_platform\Core\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SerializerContextBuilderInterface
 *
 * @package Drupal\api_platform\Core\Serializer
 *
 *  Builds the context used by the Symfony Serializer.
 */
interface SerializerContextBuilderInterface {
  /**
   * Creates a serialization context from a Request.
   *
   * @throws RuntimeException
   */
  public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array;

}
