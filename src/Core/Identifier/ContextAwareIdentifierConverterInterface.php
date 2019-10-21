<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Identifier;

/**
 * Gives access to the context in the IdentifierConverter.
 */
interface ContextAwareIdentifierConverterInterface extends IdentifierConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert(string $data, string $class, array $context = []): array;
}
