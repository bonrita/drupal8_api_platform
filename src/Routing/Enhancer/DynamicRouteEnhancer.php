<?php


namespace Drupal\api_platform\Routing\Enhancer;


use Drupal\api_platform\Core\Api\FormatsProviderInterface;
use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Drupal\api_platform\Core\Util\RequestAttributesExtractor;
use Drupal\api_platform\DynamicPathTrait;
use Drupal\Core\Routing\EnhancerInterface;
use Symfony\Component\HttpFoundation\Request;

class DynamicRouteEnhancer implements EnhancerInterface {
  use DynamicPathTrait;

  /**
   * @var \Drupal\api_platform\Core\Api\FormatsProviderInterface
   */
  private $formatsProvider;

  private $formats = [];


  /**
   * @throws InvalidArgumentException
   */
  public function __construct(FormatsProviderInterface $formatsProvider) {
    $this->formatsProvider = $formatsProvider;
  }

  /**
   * @inheritDoc
   */
  public function enhance(array $defaults, Request $request) {

    if($this->isDynamicApiPath($request)) {
      $format_parts = explode('.', $this->pathEnd);
      $this->formats = $this->formatsProvider->getFormatsFromAttributes(
        RequestAttributesExtractor::extractAttributes($request)
      );

      if (array_key_exists($format_parts[1], $this->formats)) {
        $defaults["_format"] = $format_parts[1];
      }
    }

    return $defaults;
  }

}
