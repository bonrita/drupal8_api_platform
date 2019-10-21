<?php

declare(strict_types=1);


namespace Drupal\api_platform\Core\Identifier;

use Drupal\api_platform\Core\Api\IdentifiersExtractorInterface;
use Drupal\api_platform\Core\Exception\InvalidIdentifierException;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Symfony\Component\PropertyInfo\Type;

/**
 * Identifier converter that chains identifier denormalizers.
 *
 * @author Antoine Bluchet <soyuka@gmail.com>
 */
final class IdentifierConverter implements ContextAwareIdentifierConverterInterface {

  private $propertyMetadataFactory;
  private $identifiersExtractor;
  private $identifierDenormalizers;
  private $resourceMetadataFactory;

  public function __construct(IdentifiersExtractorInterface $identifiersExtractor, PropertyMetadataFactoryInterface $propertyMetadataFactory, $identifierDenormalizers, ResourceMetadataFactoryInterface $resourceMetadataFactory = null)
  {
    $this->propertyMetadataFactory = $propertyMetadataFactory;
    $this->identifiersExtractor = $identifiersExtractor;
    $this->identifierDenormalizers = $identifierDenormalizers;
    $this->resourceMetadataFactory = $resourceMetadataFactory;
  }


  /**
   * @inheritDoc
   */
  public function convert(
    string $data,
    string $class,
    array $context = []
  ): array {
    $keys = $this->identifiersExtractor->getIdentifiersFromResourceClass($class);

    if (($numIdentifiers = \count($keys)) > 1) {
      $identifiers = CompositeIdentifierParser::parse($data);
    } elseif (0 === $numIdentifiers) {
      throw new InvalidIdentifierException(sprintf('Resource "%s" has no identifiers.', $class));
    } else {
      $identifiers = [$keys[0] => $data];
    }

    // Normalize every identifier (DateTime, UUID etc.)
    foreach ($keys as $key) {
      if (!isset($identifiers[$key])) {
        throw new InvalidIdentifierException(sprintf('Invalid identifier "%1$s", "%1$s" was not found.', $key));
      }

      $metadata = $this->getIdentifierMetadata($class, $key);
      foreach ($this->identifierDenormalizers as $normalizer) {
        if (!$normalizer->supportsDenormalization($identifiers[$key], $metadata)) {
          continue;
        }

        try {
          $identifiers[$key] = $normalizer->denormalize($identifiers[$key], $metadata);
        } catch (InvalidIdentifierException $e) {
          throw new InvalidIdentifierException(sprintf('Identifier "%s" could not be denormalized.', $key), $e->getCode(), $e);
        }
      }
    }

    return $identifiers;
  }

  private function getIdentifierMetadata($class, $propertyName)
  {
    if (!$type = $this->propertyMetadataFactory->create($class, $propertyName)->getType()) {
      return null;
    }

    return Type::BUILTIN_TYPE_OBJECT === ($builtInType = $type->getBuiltinType()) ? $type->getClassName() : $builtInType;
  }

}
