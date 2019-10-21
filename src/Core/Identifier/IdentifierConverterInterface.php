<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Identifier;

use Drupal\api_platform\Core\Exception\InvalidIdentifierException;

/**
 * Identifier converter.
 */
interface IdentifierConverterInterface
{
    /**
     * @internal
     */
    public const HAS_IDENTIFIER_CONVERTER = 'has_identifier_converter';

    /**
     * @param string $data  Identifier to convert to php values
     * @param string $class The class to which the identifiers belong
     *
     * @throws InvalidIdentifierException
     *
     * @return array Indexed by identifiers properties with their values denormalized
     */
    public function convert(string $data, string $class): array;
}
