<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Routing;


use Drupal\api_platform\Core\Api\IriConverterInterface;
use Drupal\api_platform\Core\Api\UrlGeneratorInterface;
use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Drupal\api_platform\Core\Exception\ItemNotFoundException;
use Drupal\api_platform\Core\Exception\RuntimeException;

final class IriConverter implements IriConverterInterface {

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
    // TODO: Implement getIriFromItem() method.
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

}
