<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\PathResolver;

use Drupal\api_platform\Core\Api\OperationType;
use Drupal\api_platform\Core\Api\OperationTypeDeprecationHelper;
use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Drupal\api_platform\Core\Operation\PathSegmentNameGeneratorInterface;

/**
 * Class OperationPathResolver
 *
 * @package Drupal\api_platform\Core\PathResolver
 *
 *  Generates an operation path.
 */
final class OperationPathResolver implements OperationPathResolverInterface {

  private $pathSegmentNameGenerator;

  public function __construct(PathSegmentNameGeneratorInterface $pathSegmentNameGenerator)
  {
    $this->pathSegmentNameGenerator = $pathSegmentNameGenerator;
  }


  /**
   * @inheritDoc
   */
  public function resolveOperationPath(string $resourceShortName, array $operation, $operationType): string {
    if (\func_num_args() < 4) {
      @trigger_error(sprintf('Method %s() will have a 4th `string $operationName` argument in version 3.0. Not defining it is deprecated since 2.1.', __METHOD__), E_USER_DEPRECATED);
    }

    $operationType = OperationTypeDeprecationHelper::getOperationType($operationType);

    if (OperationType::SUBRESOURCE === $operationType) {
      throw new InvalidArgumentException('Subresource operations are not supported by the OperationPathResolver.');
    }

    $path = '/'.$this->pathSegmentNameGenerator->getSegmentName($resourceShortName);

    if (OperationType::ITEM === $operationType) {
      $path .= '/{id}';
    }

//    $path .= '.{_format}';

    return $path;
  }

}
