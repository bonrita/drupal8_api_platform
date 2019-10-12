<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Resource\Factory;


use Drupal\api_platform\Core\Metadata\Resource\ResourceMetadata;

/**
 * Guesses the short name from the class name if not already set.
 */
class ShortNameResourceMetadataFactory implements ResourceMetadataFactoryInterface {
  private $decorated;

  public function __construct(ResourceMetadataFactoryInterface $decorated)
  {
    $this->decorated = $decorated;
  }

  /**
   * {@inheritdoc}
   */
  public function create(string $resourceClass): ResourceMetadata
  {
    $resourceMetadata = $this->decorated->create($resourceClass);

    if (null !== $resourceMetadata->getShortName()) {
      return $resourceMetadata;
    }

    if (false !== $pos = strrpos($resourceClass, '\\')) {
      return $resourceMetadata->withShortName(substr($resourceClass, $pos + 1));
    }

    return $resourceMetadata->withShortName($resourceClass);
  }

}
