<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Hydra\Serializer;

use Drupal\api_platform\Core\Api\UrlGeneratorInterface;
use Drupal\api_platform\Core\Problem\Serializer\ErrorNormalizerTrait;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Converts {@see \Exception} or {@see \Symfony\Component\Debug\Exception\FlattenException} to a Hydra error representation.
 */
final class ErrorNormalizer implements NormalizerInterface
{
    use ErrorNormalizerTrait;

    public const FORMAT = 'jsonld';
    public const TITLE = 'title';

    private $urlGenerator;
    private $debug = TRUE;
    private $defaultContext = [self::TITLE => 'An error occurred'];

    public function __construct(UrlGeneratorInterface $urlGenerator, array $defaultContext = [])
    {
        $this->urlGenerator = $urlGenerator;
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = [
            '@context' => $this->urlGenerator->generate('api_platform.api_jsonld_context', ['shortName' => 'Error']),
            '@type' => 'hydra:Error',
            'hydra:title' => $context[self::TITLE] ?? $this->defaultContext[self::TITLE],
            'hydra:description' => $this->getErrorMessage($object, $context, $this->debug),
        ];

        if ($this->debug && null !== $trace = $object->getTrace()) {
            $data['trace'] = $trace;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return self::FORMAT === $format && ($data instanceof \Exception || $data instanceof FlattenException);
    }

}
