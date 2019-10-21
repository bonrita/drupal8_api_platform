<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Serializer;

use Drupal\api_platform\Core\Api\OperationType;
use Drupal\api_platform\Core\Exception\RuntimeException;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Swagger\Serializer\DocumentationNormalizer;
use Drupal\api_platform\Core\Util\RequestAttributesExtractor;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class SerializerContextBuilder implements SerializerContextBuilderInterface {

  private $resourceMetadataFactory;

  public function __construct(ResourceMetadataFactoryInterface $resourceMetadataFactory)
  {
    $this->resourceMetadataFactory = $resourceMetadataFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function createFromRequest(Request $request, bool $normalization, array $attributes = null): array
  {
    if (null === $attributes) {
      throw new RuntimeException('Request attributes are not valid.');
    }

    $resourceMetadata = $this->resourceMetadataFactory->create($attributes['resource_class']);

    $key = $normalization ? 'normalization_context' : 'denormalization_context';
    if (isset($attributes['collection_operation_name'])) {
      $operationKey = 'collection_operation_name';
      $operationType = OperationType::COLLECTION;
    } elseif (isset($attributes['item_operation_name'])) {
      $operationKey = 'item_operation_name';
      $operationType = OperationType::ITEM;
    } else {
      $operationKey = 'subresource_operation_name';
      $operationType = OperationType::SUBRESOURCE;
    }

    $context = $resourceMetadata->getTypedOperationAttribute($operationType, $attributes[$operationKey], $key, [], true);
    $context['operation_type'] = $operationType;
    $context[$operationKey] = $attributes[$operationKey];

    $context['resource_class'] = $attributes['resource_class'];
    $context['input'] = $resourceMetadata->getTypedOperationAttribute($operationType, $attributes[$operationKey], 'input', $resourceMetadata->getAttribute('input'));
    $context['output'] = $resourceMetadata->getTypedOperationAttribute($operationType, $attributes[$operationKey], 'output', $resourceMetadata->getAttribute('output'));
    $context['request_uri'] = $request->getRequestUri();
    $context['uri'] = $request->getUri();

    unset($context[DocumentationNormalizer::SWAGGER_DEFINITION_NAME]);

    return $context;
  }

}
