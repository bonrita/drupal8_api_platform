<?php


namespace Drupal\api_platform\Entity;


use Drupal\api_platform\Core\Annotation\ApiResource;
use Drupal\Core\Annotation\PluralTranslation;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\taxonomy\Entity\Term as CoreTerm;
use Drupal\user\StatusItem;

/**
 * Defines the taxonomy term entity.
 *
 * @ApiResource()
 *
 */
class Term extends CoreTerm {

}
