<?php
declare(strict_types=1);

namespace Drupal\api_platform\PathProcessor;


use Drupal\api_platform\DynamicPathTrait;
use Drupal\api_platform\Routing\PathProperties;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\PathUtil\Path;

// implements OutboundPathProcessorInterface
class PathProcessor  implements OutboundPathProcessorInterface, InboundPathProcessorInterface {
  use DynamicPathTrait;

  private $configuredFormats;
  private $resourceMetadataFactory;

  /**
   * @var \Drupal\api_platform\Routing\PathProperties
   */
  private $pathProperties;

  public function __construct(array $configuredFormats, PathProperties $pathProperties)
  {
    $this->configuredFormats = $configuredFormats;
    $this->pathProperties = $pathProperties;
  }

  /**
   * @inheritDoc
   */
  public function processOutbound(
    $path,
    &$options = [],
    Request $request = NULL,
    BubbleableMetadata $bubbleable_metadata = NULL
  ) {

    if (isset($options['query']['_format_route'])) {
      $path = '/api/index.' . $options['query']['_  route_format'];
      $options = [];
    }
    return $path;
  }

  /**
   * @inheritDoc
   */
  public function processInbound($path, Request $request) {

    if ($this->isDynamicApiPath($request) ) {
      $basePath = '';
      if (strpos($path, '.') !== FALSE) {
        $parts = explode('.', $path);

        if (array_key_exists($parts[1], $this->configuredFormats)) {
          $this->pathProperties->format = $parts[1];
          $basePath = $parts[0];
        }
      }

      if (empty($basePath)) {
        $path = '/api';
      } else {
        $path = $basePath;
      }

    }
    return $path;
  }

}
