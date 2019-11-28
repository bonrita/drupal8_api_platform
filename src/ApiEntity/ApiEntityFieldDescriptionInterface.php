<?php

declare(strict_types=1);

namespace Drupal\api_platform\ApiEntity;


interface ApiEntityFieldDescriptionInterface {

  /**
   * Get field descriptions.
   *
   * The key is the field name and the value is the field description.
   *
   * @param array $context
   *   The current context.
   *
   * @param string $property
   *   The field name.
   *
   * @return array
   *   The entity field descriptions
   */
  public function accessFieldDescription(array $context = [], string $property = NULL): ?string;

}
