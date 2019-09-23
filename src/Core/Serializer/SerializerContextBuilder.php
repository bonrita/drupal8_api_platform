<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Serializer;

use Symfony\Component\HttpFoundation\Request;

class SerializerContextBuilder implements SerializerContextBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function createFromRequest(Request $request, bool $normalization, array $attributes = null): array
  {

  }

}
