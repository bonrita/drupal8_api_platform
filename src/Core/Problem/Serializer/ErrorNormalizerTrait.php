<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Problem\Serializer;

use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;

trait ErrorNormalizerTrait
{
    private function getErrorMessage($object, array $context, bool $debug = false): string
    {
        $message = $object->getMessage();

        if ($debug) {
            return $message;
        }

        if ($object instanceof FlattenException) {
            $statusCode = $context['statusCode'] ?? $object->getStatusCode();
            if ($statusCode >= 500 && $statusCode < 600) {
                $message = Response::$statusTexts[$statusCode];
            }
        }

        return $message;
    }
}
