<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\EventListener;

use Drupal\api_platform\Core\Util\RequestAttributesExtractor;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as BaseExceptionListener;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Handles requests errors.
 */
final class ExceptionListener implements EventSubscriberInterface {

    private $exceptionListener;

    public function __construct($controller, LoggerInterface $logger = null, $debug = false)
    {
        $this->exceptionListener = new BaseExceptionListener($controller, $logger, $debug);
    }

    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $request = $event->getRequest();
        // Normalize exceptions only for routes managed by API Platform

      $gg = RequestAttributesExtractor::extractAttributes($request);
      $ff = $request->attributes->getBoolean('_api_respond', false);
      $ee = $request->getRequestFormat('');
      $uu = (RequestAttributesExtractor::extractAttributes($request)['respond'] ?? $request->attributes->getBoolean('_api_respond', false));
      $gg = $request->attributes->getBoolean('_graphql', false);
      $rr = RequestAttributesExtractor::extractAttributes($request)['respond'];

        if (
            'html' === $request->getRequestFormat('') ||
            !((RequestAttributesExtractor::extractAttributes($request)['respond'] ?? $request->attributes->getBoolean('_api_respond', false)) || $request->attributes->getBoolean('_graphql', false))
        ) {
            return;
        }

        $this->exceptionListener->onKernelException($event);
    }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    // -96
    $events[KernelEvents::EXCEPTION][] = ['onKernelException', -69];

    return $events;
  }


}
