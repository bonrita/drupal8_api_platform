<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Drupal\api_platform\Core\Api\OperationAwareFormatsProviderInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;


final class FormatsProvider implements FormatsProviderInterface, OperationAwareFormatsProviderInterface {

  private $configuredFormats;
  private $resourceMetadataFactory;

  public function __construct(ResourceMetadataFactoryInterface $resourceMetadataFactory, array $configuredFormats)
  {
    $this->resourceMetadataFactory = $resourceMetadataFactory;
    $this->configuredFormats = $configuredFormats;
  }

  /**
   * @inheritDoc
   */
  public function getFormatsFromAttributes(array $attributes): array {
    if (!$attributes || !isset($attributes['resource_class'])) {
      return $this->configuredFormats;
    }

  }

  /**
   * @inheritDoc
   */
  public function getFormatsFromOperation(
    string $resourceClass,
    string $operationName,
    string $operationType
  ): array {
    $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);

    if (!$formats = $resourceMetadata->getTypedOperationAttribute($operationType, $operationName, 'formats', [], true)) {
      return $this->configuredFormats;
    }

    if (!\is_array($formats)) {
      throw new InvalidArgumentException(sprintf("The 'formats' attributes must be an array, %s given for resource class '%s'.", \gettype($formats), $resourceClass));
    }

    return $this->getOperationFormats($formats);
  }

}
