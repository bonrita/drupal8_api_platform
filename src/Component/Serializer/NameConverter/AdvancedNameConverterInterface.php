<?php

namespace Drupal\api_platform\Component\Serializer\NameConverter;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Gives access to the class, the format and the context in the property name converters.
 *
 */
interface AdvancedNameConverterInterface extends NameConverterInterface
{
  /**
   * {@inheritdoc}
   */
  public function normalize($propertyName, string $class = null, string $format = null, array $context = []);

  /**
   * {@inheritdoc}
   */
  public function denormalize($propertyName, string $class = null, string $format = null, array $context = []);
}
