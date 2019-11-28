<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\PathResolver;

use Drupal\api_platform\Core\Api\OperationType;
use Drupal\api_platform\Core\Api\OperationTypeDeprecationHelper;
use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
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

  /**
   * @var \Drupal\api_platform\Core\Api\ResourceClassResolverInterface
   */
  private $resourceClassResolver;

  public function __construct(PathSegmentNameGeneratorInterface $pathSegmentNameGenerator, ResourceClassResolverInterface $resourceClassResolver)
  {
    $this->pathSegmentNameGenerator = $pathSegmentNameGenerator;
    $this->resourceClassResolver = $resourceClassResolver;
  }


  /**
   * @inheritDoc
   */
  public function resolveOperationPath(string $resourceShortName, array $operation, $operationType, string $resourceClass, string $operationName = null, string $entityBundle = NULL): string {
    if (\func_num_args() < 4) {
      @trigger_error(sprintf('Method %s() will have a 4th `string $operationName` argument in version 3.0. Not defining it is deprecated since 2.1.', __METHOD__), E_USER_DEPRECATED);
    }

    $operationType = OperationTypeDeprecationHelper::getOperationType($operationType);

    if (OperationType::SUBRESOURCE === $operationType) {
      throw new InvalidArgumentException('Subresource operations are not supported by the OperationPathResolver.');
    }

    $path = '/'.$this->pathSegmentNameGenerator->getSegmentName($resourceShortName);

    if (!empty($entityBundle)) {
      $path .= "/{$entityBundle}";
    }

    if (OperationType::ITEM === $operationType) {
      $idKey = $this->resourceClassResolver->getIdKey($resourceClass);
//      $path .= '/{id}';
      $path .= "/{{$idKey}}";
    }

//    $path .= '.{_format}';

    return $path;
  }

}
