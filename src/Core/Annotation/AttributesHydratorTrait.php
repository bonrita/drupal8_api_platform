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

  /**
   * @throws InvalidArgumentException
   */
  private function hydrateAttributes(array $values): void
  {
    if (isset($values['attributes'])) {
      $this->attributes = $values['attributes'];
      unset($values['attributes']);
    }

    foreach ($values as $key => $value) {
      $key = (string) $key;
      if (!property_exists($this, $key)) {
        throw new InvalidArgumentException(sprintf('Unknown property "%s" on annotation "%s".', $key, self::class));
      }

      if ((new \ReflectionProperty($this, $key))->isPublic()) {
        $this->{$key} = $value;
        continue;
      }

      if (!\is_array($this->attributes)) {
        $this->attributes = [];
      }

      $this->attributes += [Inflector::tableize($key) => $value];
    }
  }

}
