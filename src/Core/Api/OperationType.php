<?php


declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

final class OperationType
{
    public const ITEM = 'item';
    public const COLLECTION = 'collection';
    public const SUBRESOURCE = 'subresource';
    public const TYPES = [self::ITEM, self::COLLECTION, self::SUBRESOURCE];
}
