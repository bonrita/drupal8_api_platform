<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\PathResolver;

/**
 * Interface OperationPathResolverInterface
 *
 * @package Drupal\api_platform\Core\PathResolver
 *
 * Resolves the path of a resource operation.
 */
interface OperationPathResolverInterface {

  /**
   * Resolves the operation path.
   *
   * @param string $resourceShortName When the operation type is a subresource and the operation has more than one identifier, this value is the previous operation path
   * @param array $operation The operation metadata
   * @param string|bool $operationType One of the constants defined in ApiPlatform\Core\Api\OperationType
   *                                       If the property is a boolean, true represents OperationType::COLLECTION, false is for OperationType::ITEM
   * @param string $resourceClass
   * @param string|null $operationName
   * @param string|null $entityBundle
   *
   * @return string
   */
  public function resolveOperationPath(string $resourceShortName, array $operation, $operationType, string $resourceClass, string $operationName = null, string $entityBundle = NULL): string;

}
