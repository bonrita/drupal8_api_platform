<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\EventListener;

use Drupal\api_platform\Core\Util\RequestAttributesExtractor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class RespondListener
 *
 * @package Drupal\api_platform\Core\EventListener
 *
 * Builds the response object.
 */
final class RespondListener implements EventSubscriberInterface {

  public const METHOD_TO_CODE = [
    'POST' => Response::HTTP_CREATED,
    'DELETE' => Response::HTTP_NO_CONTENT,
  ];

  /**
   * Creates a Response to send to the client according to the requested format.
   */
  public function onKernelView(GetResponseForControllerResultEvent $event): void
  {

    $controllerResult = $event->getControllerResult();
    $request = $event->getRequest();

    $attributes = RequestAttributesExtractor::extractAttributes($request);
    if ($controllerResult instanceof Response && ($attributes['respond'] ?? false)) {
      $event->setResponse($controllerResult);

      return;
    }
    if ($controllerResult instanceof Response || !($attributes['respond'] ?? $request->attributes->getBoolean('_api_respond', false))) {
      return;
    }

    $headers = [
      'Content-Type' => sprintf('%s; charset=utf-8', $request->getMimeType($request->getRequestFormat())),
      'Vary' => 'Accept',
      'X-Content-Type-Options' => 'nosniff',
      'X-Frame-Options' => 'deny',
    ];

    $status = null;

    $event->setResponse(new Response(
      $controllerResult,
      $status ?? self::METHOD_TO_CODE[$request->getMethod()] ?? Response::HTTP_OK,
      $headers
    ));

  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::VIEW][] = ['onKernelView', 8];

    return $events;
  }

}
