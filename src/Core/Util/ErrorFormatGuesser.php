<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Util;

use Symfony\Component\HttpFoundation\Request;

/**
 * Guesses the error format to use.
 */
final class ErrorFormatGuesser
{
    private function __construct()
    {
    }

    /**
     * Get the error format and its associated MIME type.
     */
    public static function guessErrorFormat(Request $request, array $errorFormats): array
    {
        $requestFormat = $request->getRequestFormat('');

        if ('' !== $requestFormat && isset($errorFormats[$requestFormat])) {
            return ['key' => $requestFormat, 'value' => $errorFormats[$requestFormat]];
        }

        $requestMimeTypes = Request::getMimeTypes($request->getRequestFormat());
        $defaultFormat = [];

        foreach ($errorFormats as $format => $errorMimeTypes) {
            if (array_intersect($requestMimeTypes, $errorMimeTypes)) {
                return ['key' => $format, 'value' => $errorMimeTypes];
            }

            if (!$defaultFormat) {
                $defaultFormat = ['key' => $format, 'value' => $errorMimeTypes];
            }
        }

        return $defaultFormat;
    }
}
