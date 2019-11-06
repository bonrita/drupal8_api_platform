<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\JsonLd\Serializer;

use Drupal\api_platform\Core\JsonLd\AnonymousContextBuilderInterface;
use Drupal\api_platform\Core\JsonLd\ContextBuilderInterface;

/**
 * Creates and manipulates the Serializer context.
 *
 * @internal
 */
trait JsonLdContextTrait
{
    /**
     * Updates the given JSON-LD document to add its @context key.
     */
    private function addJsonLdContext(ContextBuilderInterface $contextBuilder, string $resourceClass, array &$context, array $data = []): array
    {
        if (isset($context['jsonld_has_context'])) {
            return $data;
        }

        $context['jsonld_has_context'] = true;

        if (isset($context['jsonld_embed_context'])) {
            $data['@context'] = $contextBuilder->getResourceContext($resourceClass);

            return $data;
        }

        $data['@context'] = $contextBuilder->getResourceContextUri($resourceClass);

        return $data;
    }

}
