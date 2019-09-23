<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Routing;


use Drupal\api_platform\Core\Api\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\Routing\RouterInterface;

final class Router implements UrlGeneratorInterface{

  public const CONST_MAP = [
    UrlGeneratorInterface::ABS_URL => RouterInterface::ABSOLUTE_URL,
    UrlGeneratorInterface::ABS_PATH => RouterInterface::ABSOLUTE_PATH,
    UrlGeneratorInterface::REL_PATH => RouterInterface::RELATIVE_PATH,
    UrlGeneratorInterface::NET_PATH => RouterInterface::NETWORK_PATH,
  ];

  private $router;
  private $urlGenerator;

  public function __construct(RouterInterface $router, RequestContextAwareInterface $url_generator)
  {
    $this->router = $router;
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function generate($name, $parameters = [], $referenceType = self::ABS_PATH)
  {
     return $this->urlGenerator->generate($name, $parameters, self::CONST_MAP[$referenceType]);
//    return $this->router->generate($name, $parameters, self::CONST_MAP[$referenceType]);
  }

}
