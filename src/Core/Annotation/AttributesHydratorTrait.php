<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Annotation;

use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Doctrine\Common\Inflector\Inflector;

/**
 * Hydrates attributes from annotation's parameters.
 *
 * @internal
 */
trait AttributesHydratorTrait
{
    /**
     * @var array
     */
    public $attributes = null;

}
