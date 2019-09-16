<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Documentation;

use Drupal\api_platform\Core\Metadata\Resource\ResourceNameCollection;

/**
 * Class Documentation
 *
 * @package Drupal\api_platform\Core\Documentation
 *
 * Generates the API documentation.
 */
final class Documentation {

  private $resourceNameCollection;
  private $title;
  private $description;
  private $version;
  private $mimeTypes = [];

  public function __construct(ResourceNameCollection $resourceNameCollection, string $title = '', string $description = '', string $version = '', array $formats = [])
  {
    $this->resourceNameCollection = $resourceNameCollection;
    $this->title = $title;
    $this->description = $description;
    $this->version = $version;
    foreach ($formats as $mimeTypes) {
      foreach ($mimeTypes as $mimeType) {
        $this->mimeTypes[] = $mimeType;
      }
    }
  }


  public function getMimeTypes(): array
  {
    return $this->mimeTypes;
  }

  public function getVersion(): string
  {
    return $this->version;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function getTitle(): string
  {
    return $this->title;
  }

  public function getResourceNameCollection(): ResourceNameCollection
  {
    return $this->resourceNameCollection;
  }

}
