<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Annotation;


use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Drupal wrapped entity annotation.
 *
 * @Annotation
 * @Target({"CLASS"})
 */
final class ApiEntity {

  /**
   * @var string
   */
  public $entityClass;

  public function __construct($options = []) {
    if (!\is_string($options['entity_class'] ?? null)) {
      throw new InvalidArgumentException('This annotation needs a value representing the ContentEntityInterface class.');
    }

    if (!is_a($options['entity_class'], ContentEntityInterface::class, true)) {
      throw new InvalidArgumentException(sprintf('The entity class "%s" does not implement "%s".', $options['entity_class'], ContentEntityInterface::class));
    }

    $this->entityClass = $options['entity_class'];
    unset($options['entity_class']);

    foreach ($options as $key => $value) {
      if (!property_exists($this, $key)) {
        throw new InvalidArgumentException(sprintf('Property "%s" does not exist on the ApiEntity annotation.', $key));
      }

      $this->{$key} = $value;
    }

  }

}
