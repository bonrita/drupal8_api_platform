<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Hydra\Serializer;


use Drupal\api_platform\Core\Api\IriConverterInterface;
use Drupal\api_platform\Core\Api\OperationType;
use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\api_platform\Core\JsonLd\ContextBuilderInterface;
use Drupal\api_platform\Core\JsonLd\Serializer\JsonLdContextTrait;
use Drupal\api_platform\Core\Serializer\ContextTrait;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class CollectionNormalizer
 *
 * This normalizer handles collections.
 *
 * @package Drupal\api_platform\Core\Hydra\Serializer
 */
final class CollectionNormalizer implements NormalizerInterface, NormalizerAwareInterface {

  use NormalizerAwareTrait;
  use ContextTrait;
  use JsonLdContextTrait;

  public const FORMAT = 'jsonld';

  private $contextBuilder;
  private $resourceClassResolver;
  private $iriConverter;

  public function __construct(ContextBuilderInterface $contextBuilder, ResourceClassResolverInterface $resourceClassResolver, IriConverterInterface $iriConverter)
  {
    $this->contextBuilder = $contextBuilder;
    $this->resourceClassResolver = $resourceClassResolver;
    $this->iriConverter = $iriConverter;
  }

  /**
   * @inheritDoc
   */
  public function supportsNormalization($data, $format = NULL) {
    return self::FORMAT === $format && is_iterable($data);
  }

  /**
   * @inheritDoc
   */
  public function normalize($object, $format = NULL, array $context = []) {

    if (!isset($context['resource_class']) || isset($context['api_sub_level'])) {
      return $this->normalizeRawCollection($object, $format, $context);
    }

    $resourceClass = $this->resourceClassResolver->getResourceClass($object, $context['resource_class']);
    $context = $this->initContext($resourceClass, $context);
    $data = $this->addJsonLdContext($this->contextBuilder, $resourceClass, $context);

    if (isset($context['operation_type']) && OperationType::SUBRESOURCE === $context['operation_type']) {
      $data['@id'] = $this->iriConverter->getSubresourceIriFromResourceClass($resourceClass, $context);
    } else {
      $data['@id'] = $this->iriConverter->getIriFromResourceClass($resourceClass);
    }

    $data['@type'] = 'hydra:Collection';

    $data['hydra:member'] = [];
    foreach ($object as $obj) {
      $data['hydra:member'][] = $this->normalizer->normalize($obj, $format, $context);
    }

    if (\is_array($object)) {
      $data['hydra:totalItems'] =  \count($object);
    }

    return $data;
  }

  /**
   * Normalizes a raw collection (not API resources).
   */
  private function normalizeRawCollection(iterable $object, ?string $format, array $context): array
  {
    $data = [];
    foreach ($object as $index => $obj) {
      $data[$index] = $this->normalizer->normalize($obj, $format, $context);
    }

    return $data;
  }

}
