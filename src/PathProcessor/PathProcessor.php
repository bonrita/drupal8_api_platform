<?php
declare(strict_types=1);

namespace Drupal\api_platform\PathProcessor;


use Drupal\api_platform\DynamicPathTrait;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;

// implements OutboundPathProcessorInterface
class PathProcessor  implements OutboundPathProcessorInterface, InboundPathProcessorInterface {
  use DynamicPathTrait;

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
      $path = '/api';
    }
    return $path;
  }

}
