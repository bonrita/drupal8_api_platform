<?php


namespace Drupal\api_platform\Core\Api;


use Drupal\Core\Entity\ContentEntityInterface;

interface ResourceEntityClassResolverInterface {

  /**
   * Derive the actual api resource class associated with the entity.
   *
   * @todo Cache results.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity instance.
   *
   * @return string|null
   *   The Api resource class
   */
  public function getClassFromObject(ContentEntityInterface $entity): ?string;

}
