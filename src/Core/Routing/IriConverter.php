<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Routing;


use Drupal\api_platform\Core\Api\OperationType;
use Drupal\api_platform\Core\Util\ResourceClassInfoTrait;
use Drupal\api_platform\Core\Api\IdentifiersExtractor;
use Drupal\api_platform\Core\Api\IdentifiersExtractorInterface;
use Drupal\api_platform\Core\Api\ResourceClassResolverInterface;
use Drupal\api_platform\Core\Routing\RouteNameResolverInterface;
use Drupal\api_platform\Core\DataProvider\ItemDataProviderInterface;
use Drupal\api_platform\Core\DataProvider\SubresourceDataProviderInterface;
use Drupal\api_platform\Core\Identifier\IdentifierConverterInterface;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use Drupal\api_platform\Core\Api\IriConverterInterface;
use Drupal\api_platform\Core\Api\UrlGeneratorInterface;
use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Drupal\api_platform\Core\Exception\ItemNotFoundException;
use Drupal\api_platform\Core\Exception\RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingExceptionInterface;
use Symfony\Component\Routing\RouterInterface;

final class IriConverter implements IriConverterInterface {

  use ResourceClassInfoTrait;

  private $routeNameResolver;
  private $router;
  private $identifiersExtractor;

  //  IdentifiersExtractorInterface $identifiersExtractor = null,
  public function __construct(PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory, PropertyMetadataFactoryInterface $propertyMetadataFactory, ItemDataProviderInterface $itemDataProvider, RouteNameResolverInterface $routeNameResolver, RouterInterface $router, PropertyAccessorInterface $propertyAccessor = null, IdentifiersExtractorInterface $identifiersExtractor = null, SubresourceDataProviderInterface $subresourceDataProvider = null, IdentifierConverterInterface $identifierConverter = null, ResourceClassResolverInterface $resourceClassResolver = null)
  {
    $this->itemDataProvider = $itemDataProvider;
    $this->routeNameResolver = $routeNameResolver;
    $this->router = $router;
    $this->identifiersExtractor = $identifiersExtractor;
    $this->subresourceDataProvider = $subresourceDataProvider;
    $this->identifierConverter = $identifierConverter;
    $this->resourceClassResolver = $resourceClassResolver;

//    if (null === $identifiersExtractor) {
//      @trigger_error(sprintf('Not injecting "%s" is deprecated since API Platform 2.1 and will not be possible anymore in API Platform 3', IdentifiersExtractorInterface::class), E_USER_DEPRECATED);
//      $this->identifiersExtractor = new IdentifiersExtractor($propertyNameCollectionFactory, $propertyMetadataFactory, $propertyAccessor ?? PropertyAccess::createPropertyAccessor());
//    }
  }


  /**
   * @inheritDoc
   */
  public function getItemFromIri(string $iri, array $context = []) {
    // TODO: Implement getItemFromIri() method.
  }

  /**
   * @inheritDoc
   */
  public function getIriFromItem(
    $item,
    int $referenceType = UrlGeneratorInterface::ABS_PATH
  ): string {
    $resourceClass = $this->getResourceClass($item);
    $routeName = $this->routeNameResolver->getRouteName($resourceClass, OperationType::ITEM);

    try {
      $identifiers = $this->generateIdentifiersUrl($this->identifiersExtractor->getIdentifiersFromItem($item), $resourceClass);
      $idKey = $this->resourceClassResolver->getIdKey($resourceClass);
      return $this->router->generate($routeName, [$idKey => implode(';', $identifiers)], $referenceType);
    } catch (RuntimeException $e) {
      throw new InvalidArgumentException(sprintf(
        'Unable to generate an IRI for the item of type "%s"',
        $resourceClass
      ), $e->getCode(), $e);
    }  catch (RoutingExceptionInterface $e) {
      throw new InvalidArgumentException(sprintf(
        'Unable to generate an IRI for the item of type "%s"',
        $resourceClass
      ), $e->getCode(), $e);
    }

  }

  /**
   * @inheritDoc
   */
  public function getIriFromResourceClass(
    string $resourceClass,
    int $referenceType = UrlGeneratorInterface::ABS_PATH
  ): string {
    // TODO: Implement getIriFromResourceClass() method.
  }

  /**
   * @inheritDoc
   */
  public function getItemIriFromResourceClass(
    string $resourceClass,
    array $identifiers,
    int $referenceType = UrlGeneratorInterface::ABS_PATH
  ): string {
    // TODO: Implement getItemIriFromResourceClass() method.
  }

  /**
   * @inheritDoc
   */
  public function getSubresourceIriFromResourceClass(
    string $resourceClass,
    array $identifiers,
    int $referenceType = UrlGeneratorInterface::ABS_PATH
  ): string {
    // TODO: Implement getSubresourceIriFromResourceClass() method.
  }

  /**
   * Generate the identifier url.
   *
   * @throws InvalidArgumentException
   *
   * @return string[]
   */
  private function generateIdentifiersUrl(array $identifiers, string $resourceClass): array
  {

    if (0 === \count($identifiers)) {
      throw new \InvalidArgumentException(sprintf(
        'No identifiers defined for resource of type "%s"',
        $resourceClass
      ));
    }

    if (1 === \count($identifiers)) {
      return [rawurlencode((string) reset($identifiers))];
    }

    $this->tryMe();
  }

}
