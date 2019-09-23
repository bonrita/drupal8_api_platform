<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Swagger\Serializer;

use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class ApiGatewayNormalizer
 *
 * @see https://docs.aws.amazon.com/apigateway/latest/developerguide/api-gateway-known-issues.html
 *
 * @package Drupal\api_platform\Core\Swagger\Serializer
 *
 * Removes features unsupported by Amazon API Gateway.
 *
 * @internal
 */
final class ApiGatewayNormalizer implements NormalizerInterface{

  public const API_GATEWAY = 'api_gateway';

  private $documentationNormalizer;
  private $defaultContext = [self::API_GATEWAY => false];

  public function __construct(NormalizerInterface $documentationNormalizer)
  {
    $this->documentationNormalizer = $documentationNormalizer;
  }

  /**
   * @inheritDoc
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $data = $this->documentationNormalizer->normalize($object, $format, $context);

    if (!\is_array($data)) {
      throw new UnexpectedValueException('Expected data to be an array');
    }

    if (!($context[self::API_GATEWAY] ?? $this->defaultContext[self::API_GATEWAY])) {
      return $data;
    }

    return $data;
  }

  /**
   * @inheritDoc
   */
  public function supportsNormalization($data, $format = NULL) {
    return $this->documentationNormalizer->supportsNormalization($data, $format);
  }

}
