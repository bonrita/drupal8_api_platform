<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Hydra\Serializer;

use Drupal\api_platform\Core\Api\Entrypoint;
use Drupal\api_platform\Core\Api\IriConverterInterface;
use Drupal\api_platform\Core\Api\UrlGeneratorInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class EntrypointNormalizer
 *
 * @package Drupal\api_platform\Core\Hydra\Serializer
 *
 *  Normalizes the API entrypoint.
 */
final class EntrypointNormalizer implements NormalizerInterface {

  public const FORMAT = 'jsonld';

  private $resourceMetadataFactory;
  private $iriConverter;
  private $urlGenerator;

  public function __construct(ResourceMetadataFactoryInterface $resourceMetadataFactory, IriConverterInterface $iriConverter, UrlGeneratorInterface $urlGenerator)
  {
    $this->resourceMetadataFactory = $resourceMetadataFactory;
    $this->iriConverter = $iriConverter;
    $this->urlGenerator = $urlGenerator;
  }

  /**
   * @inheritDoc
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $entrypoint = [
      '@context' => $this->urlGenerator->generate('api_platform.api_jsonld_context', ['shortName' => 'Entrypoint']),
      '@id' => $this->urlGenerator->generate('api_platform.api_entrypoint'),
      '@type' => 'Entrypoint',
    ];

    return $entrypoint;
  }

  /**
   * @inheritDoc
   */
  public function supportsNormalization($data, $format = NULL) {
    return self::FORMAT === $format && $data instanceof Entrypoint;
  }

}
