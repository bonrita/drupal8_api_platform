<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\PropertyInfo\Metadata\Property;

use ApiPlatform\Core\Exception\RuntimeException;
use Drupal\api_platform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyNameCollection;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

/**
 * PropertyInfo collection loader.
 *
 * This is not a decorator on purpose because it should always have the top priority.
 *
 */
final class PropertyInfoPropertyNameCollectionFactory implements PropertyNameCollectionFactoryInterface
{

}
