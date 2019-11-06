<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\JsonLd;

use Drupal\api_platform\Core\Api\UrlGeneratorInterface;

/**
 * JSON-LD context builder with Input Output DTO support interface.
 */
interface AnonymousContextBuilderInterface extends ContextBuilderInterface
{
    /**
     * Creates a JSON-LD context based on the given object.
     * Usually this is used with an Input or Output DTO object.
     */
    public function getAnonymousResourceContext($object, array $context = [], int $referenceType = UrlGeneratorInterface::ABS_PATH): array;
}
