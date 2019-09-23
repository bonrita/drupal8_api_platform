<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Serializer;

use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder as BaseJsonEncoder;

/**
 * Class JsonEncoder
 *
 * @package Drupal\api_platform\Core\Serializer
 *
 * A JSON encoder with appropriate default options to embed the generated document into HTML.
 */
final class JsonEncoder implements EncoderInterface {

  private $format;
  private $jsonEncoder;

  public function __construct(string $format) {
    $this->format = $format;

    $jsonEncode = new JsonEncode();
    $jsonDecode = new JsonDecode();

    $this->jsonEncoder = new BaseJsonEncoder($jsonEncode, $jsonDecode);
  }

  /**
   * {@inheritdoc}
   */
  public function supportsEncoding($format)
  {
   return $this->format === $format;
  }

  /**
   * {@inheritdoc}
   */
  public function encode($data, $format, array $context = [])
  {
    return $this->jsonEncoder->encode($data, $format, $context);
  }

}
