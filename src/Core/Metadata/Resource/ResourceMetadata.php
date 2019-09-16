<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Resource;

/**
 * Class ResourceMetadata
 *
 * @package Drupal\api_platform\Core\Metadata\Resource
 *
 *  Resource metadata.
 */
final class ResourceMetadata {

  private $shortName;
  private $description;
  private $iri;
  private $itemOperations;
  private $collectionOperations;
  private $subresourceOperations;
  private $graphql;
  private $attributes;

  public function __construct(string $shortName = null, string $description = null, string $iri = null, array $itemOperations = null, array $collectionOperations = null, array $attributes = null, array $subresourceOperations = null, array $graphql = null)
  {
    $this->shortName = $shortName;
    $this->description = $description;
    $this->iri = $iri;
    $this->itemOperations = $itemOperations;
    $this->collectionOperations = $collectionOperations;
    $this->subresourceOperations = $subresourceOperations;
    $this->graphql = $graphql;
    $this->attributes = $attributes;
  }

}
