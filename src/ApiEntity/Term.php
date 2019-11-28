<?php


//*   normalizationContext={"groups"={"term:read"}},
//*   denormalizationContext={"groups"={"term:write"}}

//*   itemOperations={
//  *   "get"={"path"="/categories/{tid}"},
// *   "put",
// *   "delete"
//  *   }

//*   shortName="Boxer",

namespace Drupal\api_platform\ApiEntity;


use Drupal\api_platform\Core\Annotation\ApiEntity;
use Drupal\api_platform\Core\Annotation\ApiResource;
use Drupal\Core\Annotation\PluralTranslation;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\user\StatusItem;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Wraps the taxonomy term entity.
 *
 * @ApiResource(
 * )
 * @ApiEntity(
 *   entity_class = "Drupal\taxonomy\Entity\Term"
 * )
 */
class Term extends ApiEntityBase {


  private $fullName;

  public function getFullName(array $context = []): ?string {
    return $this->fullName;
  }

//  /**
//   * @Groups({"term:read"})
//   */
//  public function apiFields() {
//    return [
//      'name',
//      'status',
//      'field_opinion',
//      'vid'
//    ];
//  }

//  /**
//   * @Groups({"term:write"})
//   */
//  public function apiWriteFields() {
//    return [
//      'name',
//    ];
//  }

  /**
   * @inheritDoc
   */
  public function accessFieldDescription(array $context = [], string $property = NULL): ?string {
    $description = parent::accessFieldDescription($context, $property);

    if (empty($description) && 'name' === $property) {
      $description = 'The category name';
    }

    return $description;
  }

}
