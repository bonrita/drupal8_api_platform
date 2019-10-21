<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\DataProvider;

use Drupal\api_platform\Core\Exception\ResourceClassNotSupportedException;

/**
 * Tries each configured data provider and returns the result of the first able to handle the resource class.
 */
final class ChainItemDataProvider implements ItemDataProviderInterface {

  /**
   * @inheritDoc
   */
  public function getItem(
    string $resourceClass,
    $id,
    string $operationName = NULL,
    array $context = []
  ) {
    // TODO: Implement getItem() method.
  }

}
