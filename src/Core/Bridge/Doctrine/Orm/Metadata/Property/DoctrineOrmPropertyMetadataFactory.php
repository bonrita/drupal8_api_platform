<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Bridge\Doctrine\Orm\Metadata\Property;

use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Property\PropertyMetadata;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Entity\Exception\NoCorrespondingEntityClassException;

/**
 * Use Doctrine metadata to populate the identifier property.
 */
final class DoctrineOrmPropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    private $decorated;
    private $managerRegistry;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  public function __construct(EntityTypeRepositoryInterface $entityTypeRepository, EntityTypeManagerInterface $entityTypeManager , PropertyMetadataFactoryInterface $decorated)
    {
        $this->managerRegistry = $entityTypeRepository;
        $this->decorated = $decorated;
        $this->entityTypeManager = $entityTypeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $resourceClass, string $property, array $options = []): PropertyMetadata
    {
        $propertyMetadata = $this->decorated->create($resourceClass, $property, $options);

        if (null !== $propertyMetadata->isIdentifier()) {
            return $propertyMetadata;
        }

        try {
          $entityTypeID = $this->managerRegistry->getEntityTypeFromClass($resourceClass);
          if($this->entityTypeManager->getDefinition($entityTypeID)->getKey('id')){
            $propertyMetadata = $propertyMetadata->withIdentifier(TRUE);
            $propertyMetadata = $propertyMetadata->withWritable(TRUE);
          } else {
            $propertyMetadata = $propertyMetadata->withIdentifier(false);
          }



          $gg = 0;
        } catch (NoCorrespondingEntityClassException $e) {
          return $propertyMetadata;
        }

        return $propertyMetadata;

        $manager = $this->managerRegistry->getManagerForClass($resourceClass);
        if (!$manager) {
            return $propertyMetadata;
        }
        $doctrineClassMetadata = $manager->getClassMetadata($resourceClass);

        $identifiers = $doctrineClassMetadata->getIdentifier();
        foreach ($identifiers as $identifier) {
            if ($identifier === $property) {
                $propertyMetadata = $propertyMetadata->withIdentifier(true);

                if (null !== $propertyMetadata->isWritable()) {
                    break;
                }

                if ($doctrineClassMetadata instanceof ClassMetadataInfo) {
                    $writable = $doctrineClassMetadata->isIdentifierNatural();
                } else {
                    $writable = false;
                }

                $propertyMetadata = $propertyMetadata->withWritable($writable);

                break;
            }
        }

        if (null === $propertyMetadata->isIdentifier()) {
            $propertyMetadata = $propertyMetadata->withIdentifier(false);
        }

        return $propertyMetadata;
    }
}
