<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Extractor;

use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Psr\Container\ContainerInterface;

/**
 * Class AbstractExtractor
 *
 * @package Drupal\api_platform\Core\Metadata\Extractor
 *
 * Base file extractor.
 */
abstract class AbstractExtractor implements ExtractorInterface {

  protected $paths;
  protected $resources;
  private $container;
  private $collectedParameters = [];

  /**
   * @param string[] $paths
   */
  public function __construct(array $paths, ContainerInterface $container = null)
  {
    $this->paths = $paths;
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public function getResources(): array
  {
    if (null !== $this->resources) {
      return $this->resources;
    }

    $this->resources = [];
    foreach ($this->paths as $path) {
      $this->extractPath($path);
    }

    return $this->resources;
  }

  /**
   * Extracts metadata from a given path.
   */
  abstract protected function extractPath(string $path);


}
