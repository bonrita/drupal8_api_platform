<?php


namespace Drupal\api_platform\EventListener;


use Drupal\api_platform\DynamicPathTrait;
use Drupal\Core\Path\CurrentPathStack;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class RouteNotFoundException implements EventSubscriberInterface {
  use DynamicPathTrait;

  /**
   * The route provider responsible for the first-pass match.
   *
   * @var \Symfony\Cmf\Component\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The list of available enhancers.
   *
   * @var \Drupal\Core\Routing\EnhancerInterface[]
   */
  protected $enhancers = [];

  /**
   * The list of available route filters.
   *
   * @var \Drupal\Core\Routing\FilterInterface[]
   */
  protected $filters = [];

  /**
   * The URL generator.
   *
   * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Constructs a new Router.
   *
   * @param \Symfony\Cmf\Component\Routing\RouteProviderInterface $route_provider
   *   The route provider.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path stack.
   * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $url_generator
   *   The URL generator.
   */
  public function __construct(RouteProviderInterface $route_provider, CurrentPathStack $current_path) {
    $this->routeProvider = $route_provider;
    $this->currentPath = $current_path;
  }

  /**
   * Redirects not found API routes.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
   *   The exception event.
   */
  public function onException(GetResponseForExceptionEvent $event) {
    $exception = $event->getException();

    if (!($exception instanceof NotFoundHttpException)) {
      return;
    }

//    $request = $event->getRequest();
//
//    if ($this->isDynamicApiPath($request)) {
//          $response = new RedirectResponse('/api', 301);
//          $event->setResponse($response);
//    }

  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[KernelEvents::EXCEPTION] = ['onException'];

    return $events;
  }

}
