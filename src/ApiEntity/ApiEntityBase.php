<?php

declare(strict_types=1);

namespace Drupal\api_platform\ApiEntity;


use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ApiEntityBase implements ApiEntityInterface, ApiEntityFieldDescriptionInterface, ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  private $entityFieldManager;

  public function __construct(EntityFieldManagerInterface $entityFieldManager) {
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager')
    );
  }

  /**
   * @inheritDoc
   */
  public function accessFieldDescription(array $context = [], string $property = NULL): ?string {
    $description = '';
    $fieldDefinition = $this->entityFieldManager->getFieldDefinitions($context['entity_type'], $context['bundle']);
    if (isset($fieldDefinition[$property])) {

      /** @var \Drupal\Core\Field\BaseFieldDefinition $definition */
      $definition = $fieldDefinition[$property];
      $description = (string) $definition->getDescription();
    }

    return $description;
  }


}
