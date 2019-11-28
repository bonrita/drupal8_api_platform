<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Validator\Metadata\Property;


use Drupal\api_platform\Core\Exception\PropertyNotFoundException;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Property\PropertyMetadata;
use Symfony\Component\Validator\Constraints\Bic;
use Symfony\Component\Validator\Constraints\CardScheme;
use Symfony\Component\Validator\Constraints\Currency;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Isbn;
use Symfony\Component\Validator\Constraints\Issn;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Time;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface as ValidatorMetadataFactoryInterface;

/**
 * Class ValidatorPropertyMetadataFactory
 *
 * Decorates a metadata loader using the validator.
 *
 * @package Drupal\api_platform\Core\Validator\Metadata\Property
 */
final class ValidatorPropertyMetadataFactory implements PropertyMetadataFactoryInterface {

  /**
   * @var string[] A list of constraint classes making the entity required
   */
  public const REQUIRED_CONSTRAINTS = [NotBlank::class, NotNull::class];

  public const SCHEMA_MAPPED_CONSTRAINTS = [
    Url::class => 'http://schema.org/url',
    Email::class => 'http://schema.org/email',
    Uuid::class => 'http://schema.org/identifier',
    CardScheme::class => 'http://schema.org/identifier',
    Bic::class => 'http://schema.org/identifier',
    Iban::class => 'http://schema.org/identifier',
    Date::class => 'http://schema.org/Date',
    DateTime::class => 'http://schema.org/DateTime',
    Time::class => 'http://schema.org/Time',
    Image::class => 'http://schema.org/image',
    File::class => 'http://schema.org/MediaObject',
    Currency::class => 'http://schema.org/priceCurrency',
    Isbn::class => 'http://schema.org/isbn',
    Issn::class => 'http://schema.org/issn',
  ];

  private $decorated;
  private $validatorMetadataFactory;

  public function __construct(ValidatorMetadataFactoryInterface $validatorMetadataFactory, PropertyMetadataFactoryInterface $decorated)
  {
    $this->validatorMetadataFactory = $validatorMetadataFactory;
    $this->decorated = $decorated;
  }

  /**
   * @inheritDoc
   */
  public function create(
    string $resourceClass,
    string $name,
    array $options = []
  ): PropertyMetadata {
    $propertyMetadata = $this->decorated->create($resourceClass, $name, $options);

    $required = $propertyMetadata->isRequired();
    $iri = $propertyMetadata->getIri();

    if (null !== $required && null !== $iri) {
      return $propertyMetadata;
    }

    return $propertyMetadata;
  }

}
