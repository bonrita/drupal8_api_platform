<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\JsonLd;

use Drupal\api_platform\Core\Api\UrlGeneratorInterface;
use Drupal\api_platform\Core\Exception\ResourceClassNotFoundException;

/**
 * JSON-LD context builder interface.
 */
interface ContextBuilderInterface
{
    public const HYDRA_NS = 'http://www.w3.org/ns/hydra/core#';
    public const RDF_NS = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    public const RDFS_NS = 'http://www.w3.org/2000/01/rdf-schema#';
    public const XML_NS = 'http://www.w3.org/2001/XMLSchema#';
    public const OWL_NS = 'http://www.w3.org/2002/07/owl#';
    public const SCHEMA_ORG_NS = 'http://schema.org/';

    /**
     * Gets the base context.
     */
    public function getBaseContext(int $referenceType = UrlGeneratorInterface::ABS_PATH): array;

    /**
     * Builds the JSON-LD context for the entrypoint.
     */
    public function getEntrypointContext(int $referenceType = UrlGeneratorInterface::ABS_PATH): array;

    /**
     * Builds the JSON-LD context for the given resource.
     *
     * @throws ResourceClassNotFoundException
     */
    public function getResourceContext(string $resourceClass, int $referenceType = UrlGeneratorInterface::ABS_PATH): array;

    /**
     * Gets the URI of the given resource context.
     */
    public function getResourceContextUri(string $resourceClass, int $referenceType = UrlGeneratorInterface::ABS_PATH): string;
}
