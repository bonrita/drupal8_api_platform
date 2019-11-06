<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\EventListener;


use Drupal\api_platform\Core\Exception\RuntimeException;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\ToggleableOperationAttributeTrait;
use Drupal\api_platform\Core\Serializer\ResourceList;
use Drupal\api_platform\Core\Serializer\SerializerContextBuilderInterface;
use Drupal\api_platform\Core\Util\RequestAttributesExtractor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class SerializeListener
 *
 * @package Drupal\api_platform\Core\EventListener
 *
 * Serializes data.
 */
class SerializeListener implements EventSubscriberInterface {

  use ToggleableOperationAttributeTrait;

  public const OPERATION_ATTRIBUTE_KEY = 'serialize';

  private $serializer;
  private $serializerContextBuilder;

  public function __construct(SerializerInterface $serializer, SerializerContextBuilderInterface $serializerContextBuilder, ResourceMetadataFactoryInterface $resourceMetadataFactory = null)
  {
    $this->serializer = $serializer;
    $this->serializerContextBuilder = $serializerContextBuilder;
    $this->resourceMetadataFactory = $resourceMetadataFactory;
  }

  /**
   * Serializes the data to the requested format.
   */
  public function onKernelView(GetResponseForControllerResultEvent $event): void
  {
    $controllerResult = $event->getControllerResult();
    $request = $event->getRequest();

    if (
      $controllerResult instanceof Response
      || !(($attributes = RequestAttributesExtractor::extractAttributes($request))['respond'] ?? $request->attributes->getBoolean('_api_respond', false))
      || $attributes && $this->isOperationAttributeDisabled($attributes, self::OPERATION_ATTRIBUTE_KEY)
    ) {
      return;
    }

    if (!$attributes) {
      $this->serializeRawData($event, $request, $controllerResult);

      return;
    }

    $context = $this->serializerContextBuilder->createFromRequest($request, true, $attributes);

    $resources = new ResourceList();
    $context['resources'] = &$resources;

    $resourcesToPush = new ResourceList();
    $context['resources_to_push'] = &$resourcesToPush;

    $request->attributes->set('_api_normalization_context', $context);

    $event->setControllerResult($this->serializer->serialize($controllerResult, $request->getRequestFormat(), $context));

    $request->attributes->set('_resources', $request->attributes->get('_resources', []) + (array) $resources);
    if (!\count($resourcesToPush)) {
      return;
    }

    $gg = $attributes;
    $gg =0;

  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::VIEW][] = ['onKernelView', 16];

    return $events;
  }

  /**
   * Tries to serialize data that are not API resources (e.g. the entrypoint or data returned by a custom controller).
   *
   * @param object $controllerResult
   *
   * @throws RuntimeException
   */
  private function serializeRawData(GetResponseForControllerResultEvent $event, Request $request, $controllerResult): void
  {
    if (\is_object($controllerResult)) {
      $event->setControllerResult($this->serializer->serialize($controllerResult, $request->getRequestFormat(), $request->attributes->get('_api_normalization_context', [])));

      return;
    }
  }



}
