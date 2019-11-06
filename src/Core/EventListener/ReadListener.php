<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\EventListener;

use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\api_platform\Core\DataProvider\CollectionDataProviderInterface;
use Drupal\api_platform\Core\DataProvider\ItemDataProviderInterface;
use Drupal\api_platform\Core\DataProvider\OperationDataProviderTrait;
use Drupal\api_platform\Core\DataProvider\SubresourceDataProviderInterface;
use Drupal\api_platform\Core\Exception\InvalidIdentifierException;
use Drupal\api_platform\Core\Exception\RuntimeException;
use Drupal\api_platform\Core\Identifier\IdentifierConverterInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\ToggleableOperationAttributeTrait;
use Drupal\api_platform\Core\Serializer\SerializerContextBuilderInterface;
use Drupal\api_platform\Core\Util\CloneTrait;
use Drupal\api_platform\Core\Util\RequestAttributesExtractor;
use Drupal\api_platform\Core\Util\RequestParser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Retrieves data from the applicable data provider and sets it as a request parameter called data.
 */
class ReadListener implements EventSubscriberInterface {
  use ToggleableOperationAttributeTrait;
  use OperationDataProviderTrait;

  public const OPERATION_ATTRIBUTE_KEY = 'read';

  private $serializerContextBuilder;

  /**
   * @var \Drupal\api_platform\Core\Api\ResourceClassResolverInterface
   */
  private $resourceClassResolver;

  public function __construct(
    CollectionDataProviderInterface $collectionDataProvider,
    ItemDataProviderInterface $itemDataProvider,
    ResourceClassResolverInterface $resourceClassResolver,
    SubresourceDataProviderInterface $subresourceDataProvider = null,
    SerializerContextBuilderInterface $serializerContextBuilder = null,
    IdentifierConverterInterface $identifierConverter = null,
    ResourceMetadataFactoryInterface $resourceMetadataFactory = null
  )
  {
    $this->collectionDataProvider = $collectionDataProvider;
    $this->itemDataProvider = $itemDataProvider;
    $this->subresourceDataProvider = $subresourceDataProvider;
    $this->serializerContextBuilder = $serializerContextBuilder;
    $this->identifierConverter = $identifierConverter;
    $this->resourceMetadataFactory = $resourceMetadataFactory;
    $this->resourceClassResolver = $resourceClassResolver;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onKernelRequest', 4];

    return $events;
  }

  /**
   * Calls the data provider and sets the data attribute.
   *
   * @throws NotFoundHttpException
   */
  public function onKernelRequest(GetResponseEvent $event): void
  {
    $request = $event->getRequest();
    if (
      !($attributes = RequestAttributesExtractor::extractAttributes($request))
      || !$attributes['receive']
      || $request->isMethod('POST') && isset($attributes['collection_operation_name'])
      || $this->isOperationAttributeDisabled($attributes, self::OPERATION_ATTRIBUTE_KEY)
    ) {
      return;
    }

    if (null === $filters = $request->attributes->get('_api_filters')) {
      $queryString = RequestParser::getQueryString($request);
      $filters = $queryString ? RequestParser::parseRequestParams($queryString) : null;
    }

    $context = null === $filters ? [] : ['filters' => $filters];
    if ($this->serializerContextBuilder) {
      // Builtin data providers are able to use the serialization context to automatically add join clauses
      $context += $normalizationContext = $this->serializerContextBuilder->createFromRequest($request, true, $attributes);
      $request->attributes->set('_api_normalization_context', $normalizationContext);
    }

    $data = [];

    if ($this->identifierConverter) {
      $context[IdentifierConverterInterface::HAS_IDENTIFIER_CONVERTER] = true;
    }

    try {
      $identifiers = $this->extractIdentifiers($request->attributes->all(), $attributes);

      if (isset($attributes['item_operation_name'])) {
        $data = $this->getItemData($identifiers, $attributes, $context);
      }

    } catch (InvalidIdentifierException $e) {
      throw new NotFoundHttpException('Not found, because of an invalid identifier configuration', $e);
    }

    if (null === $data) {
      throw new NotFoundHttpException('Not Found');
    }

    $request->attributes->set('data', $data);
  }

  private function isOperationAttributeDisabled(
    array $attributes,
    string $OPERATION_ATTRIBUTE_KEY
  ) {
    $gg = 0;
  }

}
