<?php

declare(strict_types=1);

namespace Drupal\api_platform\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class SwaggerUiListener implements EventSubscriberInterface {

  /**
   * Sets SwaggerUiAction as controller if the requested format is HTML.
   */
  public function onKernelRequest(GetResponseEvent $event): void
  {
    $request = $event->getRequest();
    if (
      'html' !== $request->getRequestFormat('') ||
      !($request->attributes->has('_api_resource_class') || $request->attributes->getBoolean('_api_respond', false))
    ) {
      return;
    }

    $request->attributes->set('_controller', 'api_platform.swagger.action.ui');
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onKernelRequest'];
    return $events;
  }

}
