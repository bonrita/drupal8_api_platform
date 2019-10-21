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
use Drupal\taxonomy\Entity\Term;
use Drupal\user\StatusItem;

/**
 * Defines the taxonomy term entity.
 *
 * @ApiResource()
 *
 */
class Car {

  private $id;

  private $year;

  private $name;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setYear(\DateTimeInterface $year): self
  {
    $this->year = $year;

    return $this;
  }
  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  public function getColor(): ?string
  {
    return $this->color;
  }

  public function setColor(string $color): self
  {
    $this->color = $color;

    return $this;
  }

}
