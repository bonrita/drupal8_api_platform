<?php


namespace Drupal\api_platform\ApiEntity;


use Drupal\api_platform\Core\Annotation\ApiEntity;
use Drupal\api_platform\Core\Annotation\ApiResource;
use Drupal\Core\Annotation\PluralTranslation;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\user\StatusItem;

/**
 * Wraps the taxonomy term entity.
 *
 * @ApiResource(
 *   shortName="Boxer"
 * )
 * @ApiEntity(
 *   entity_class = "Drupal\taxonomy\Entity\Term"
 * )
 */
class Term implements ApiEntityInterface {

}
