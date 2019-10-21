<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Util;

use Symfony\Component\HttpFoundation\Request;

/**
 * Utility functions for working with Symfony's HttpFoundation request.
 *
 * @internal

 */
final class RequestParser
{
    private function __construct()
    {
    }

    /**
     * Gets a fixed request.
     */
    public static function parseAndDuplicateRequest(Request $request): Request
    {

    }

    /**
     * Parses request parameters from the specified source.
     *
     * @author Rok Kralj
     *
     * @see https://stackoverflow.com/a/18209799/1529493
     */
    public static function parseRequestParams(string $source): array
    {

    }

    /**
     * Generates the normalized query string for the Request.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized
     * and have consistent escaping.
     */
    public static function getQueryString(Request $request): ?string
    {
        $qs = $request->server->get('QUERY_STRING', '');
        if ('' === $qs) {
            return null;
        }

    }
}
