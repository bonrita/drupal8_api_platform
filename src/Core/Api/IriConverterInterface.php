<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Drupal\api_platform\Core\Exception\ItemNotFoundException;
use Drupal\api_platform\Core\Exception\RuntimeException;

/**
 * Converts item and resources to IRI and vice versa.
 */
interface IriConverterInterface
{
    /**
     * Retrieves an item from its IRI.
     *
     * @throws InvalidArgumentException
     * @throws ItemNotFoundException
     *
     * @return object
     */
    public function getItemFromIri(string $iri, array $context = []);

    /**
     * Gets the IRI associated with the given item.
     *
     * @param object $item
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function getIriFromItem($item, int $referenceType = UrlGeneratorInterface::ABS_PATH): string;

    /**
     * Gets the IRI associated with the given resource collection.
     *
     * @throws InvalidArgumentException
     */
    public function getIriFromResourceClass(string $resourceClass, int $referenceType = UrlGeneratorInterface::ABS_PATH): string;

    /**
     * Gets the item IRI associated with the given resource.
     *
     * @throws InvalidArgumentException
     */
    public function getItemIriFromResourceClass(string $resourceClass, array $identifiers, int $referenceType = UrlGeneratorInterface::ABS_PATH): string;

    /**
     * Gets the IRI associated with the given resource subresource.
     *
     * @throws InvalidArgumentException
     */
    public function getSubresourceIriFromResourceClass(string $resourceClass, array $identifiers, int $referenceType = UrlGeneratorInterface::ABS_PATH): string;
}
