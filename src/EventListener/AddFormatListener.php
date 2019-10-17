<?php

declare(strict_types=1);

namespace Drupal\api_platform\EventListener;


use Drupal\api_platform\Core\Api\FormatMatcher;
use Drupal\api_platform\Core\Api\FormatsProviderInterface;
use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Drupal\api_platform\Core\Util\RequestAttributesExtractor;
use Negotiation\Negotiator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final class AddFormatListener implements EventSubscriberInterface {

  private $negotiator;
  private $formats = [];
  private $mimeTypes;
  private $formatsProvider;
  private $formatMatcher;

  /**
   * @throws InvalidArgumentException
   */
  public function __construct(Negotiator $negotiator, /* FormatsProviderInterface */ $formatsProvider)
  {
    $this->negotiator = $negotiator;
    if (\is_array($formatsProvider)) {
      @trigger_error('Using an array as formats provider is deprecated since API Platform 2.3 and will not be possible anymore in API Platform 3', E_USER_DEPRECATED);
      $this->formats = $formatsProvider;
    } else {
      if (!$formatsProvider instanceof FormatsProviderInterface) {
        throw new InvalidArgumentException(sprintf('The "$formatsProvider" argument is expected to be an implementation of the "%s" interface.', FormatsProviderInterface::class));
      }

        $this->formatsProvider = $formatsProvider;
    }
  }

  /**
   * Sets the applicable format to the HttpFoundation Request.
   *
   * @throws NotFoundHttpException
   * @throws NotAcceptableHttpException
   */
  public function onKernelRequest(GetResponseEvent $event): void
  {
    $request = $event->getRequest();

    $all = ($request->attributes->has('_api_resource_class') || $request->attributes->getBoolean('_api_respond', false) || $request->attributes->getBoolean('_graphql', false));
    $reso = $request->attributes->has('_api_resource_class');
    $resp = $request->attributes->getBoolean('_api_respond', false);
    $grp = $request->attributes->getBoolean('_graphql', false);

    if (!($request->attributes->has('_api_resource_class') || $request->attributes->getBoolean('_api_respond', false) || $request->attributes->getBoolean('_graphql', false))) {
      return;
    }

    // BC check to be removed in 3.0
    if (null !== $this->formatsProvider) {
      $this->formats = $this->formatsProvider->getFormatsFromAttributes(RequestAttributesExtractor::extractAttributes($request));
    }

    $this->formatMatcher = new FormatMatcher($this->formats);

    $this->populateMimeTypes();
    //    $this->addRequestFormats($request, $this->formats);

    // Empty strings must be converted to null because the Symfony router doesn't support parameter typing before 3.2 (_format)
    if (null === $routeFormat = $request->attributes->get('_format') ?: null) {
      $mimeTypes = array_keys($this->mimeTypes);
    } elseif (!isset($this->formats[$routeFormat])) {
      throw new NotFoundHttpException(sprintf('Format "%s" is not supported', $routeFormat));
    } else {
      $mimeTypes = Request::getMimeTypes($routeFormat);
    }

    // First, try to guess the format from the Accept header
    /** @var string|null $accept */
    $accept = $request->headers->get('Accept');
    if (null !== $accept) {
      if (null === $mediaType = $this->negotiator->getBest($accept, $mimeTypes)) {
        throw $this->getNotAcceptableHttpException($accept, $mimeTypes);
      }

      $request->setRequestFormat($this->formatMatcher->getFormat($mediaType->getType()));

      return;
    }

    $hh = $accept;
    $gg = 0;
  }

  /**
   * Populates the $mimeTypes property.
   */
  private function populateMimeTypes(): void
  {
    if (null !== $this->mimeTypes) {
      return;
    }

    $this->mimeTypes = [];
    foreach ($this->formats as $format => $mimeTypes) {
      foreach ($mimeTypes as $mimeType) {
        $this->mimeTypes[$mimeType] = $format;
      }
    }
  }

  /**
   * Adds the supported formats to the request.
   *
   * This is necessary for {@see Request::getMimeType} and {@see Request::getMimeTypes} to work.
   */
  private function addRequestFormats(Request $request, array $formats): void
  {
    foreach ($formats as $format => $mimeTypes) {
      $request->setFormat($format, (array) $mimeTypes);
    }
  }

  /**
   * Retrieves an instance of NotAcceptableHttpException.
   *
   * @param string[]|null $mimeTypes
   */
  private function getNotAcceptableHttpException(string $accept, array $mimeTypes = NULL): NotAcceptableHttpException {
    if (null === $mimeTypes) {
      $mimeTypes = array_keys($this->mimeTypes);
    }

    return new NotAcceptableHttpException(sprintf(
      'Requested format "%s" is not supported. Supported MIME types are "%s".',
      $accept,
      implode('", "', $mimeTypes)
    ));
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onKernelRequest', 7];
    return $events;
  }

}
