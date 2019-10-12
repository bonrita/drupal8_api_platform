<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\PathResolver;

use Drupal\api_platform\Core\Api\OperationTypeDeprecationHelper;
use Drupal\api_platform\Core\PathResolver\OperationPathResolverInterface;

/**
 * Class CustomOperationPathResolver
 *
 * @package Drupal\api_platform\Core\PathResolver
 *
 * Resolves the custom operations path.
 */
final class CustomOperationPathResolver implements OperationPathResolverInterface {

  private $deferred;

  public function __construct(OperationPathResolverInterface $deferred)
  {
    $this->deferred = $deferred;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveOperationPath(string $resourceShortName, array $operation, $operationType/*, string $operationName = null*/): string {
    if (\func_num_args() >= 4) {
      $operationName = func_get_arg(3);
    } else {
      @trigger_error(sprintf('Method %s() will have a 4th `string $operationName` argument in version 3.0. Not defining it is deprecated since 2.1.', __METHOD__), E_USER_DEPRECATED);

      $operationName = null;
    }

    return $operation['path'] ?? $this->deferred->resolveOperationPath($resourceShortName, $operation, OperationTypeDeprecationHelper::getOperationType($operationType), $operationName);
  }

}
