<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Routing;


use Drupal\api_platform\Core\Api\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\Routing\RouterInterface;

final class Router implements UrlGeneratorInterface, RouterInterface {

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

  /**
   * {@inheritdoc}
   */
  public function getRouteCollection()
  {
    return $this->router->getRouteCollection();
  }

  /**
   * {@inheritdoc}
   */
  public function match($pathInfo)
  {
    $baseContext = $this->router->getContext();
    $pathInfo = str_replace($baseContext->getBaseUrl(), '', $pathInfo);

    $request = Request::create($pathInfo, 'GET', [], [], [], ['HTTP_HOST' => $baseContext->getHost()]);
    try {
      $context = (new RequestContext())->fromRequest($request);
    } catch (RequestExceptionInterface $e) {
      throw new ResourceNotFoundException('Invalid request context.');
    }

    $context->setPathInfo($pathInfo);
    $context->setScheme($baseContext->getScheme());
    $context->setHost($baseContext->getHost());

    try {
      $this->router->setContext($context);

      return $this->router->match($request->getPathInfo());
    } finally {
      $this->router->setContext($baseContext);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setContext(RequestContext $context)
  {
    $this->router->setContext($context);
  }

  /**
   * {@inheritdoc}
   */
  public function getContext()
  {
    return $this->router->getContext();
  }

}
