<?php

declare(strict_types=1);

namespace Drupal\Tests\api_platform\Metadata\Resource\Factory;


use Drupal\api_platform\Core\Metadata\Resource\ResourceMetadata;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * Resource metadata provider for file configured factories tests.
 */
abstract class FileConfigurationMetadataFactoryProvider extends UnitTestCase {

  public function resourceMetadataProvider()
  {
    $resourceMetadata = new ResourceMetadata();

    $metadata = [
      'shortName' => 'thedummyshortname',
      'description' => 'Dummy resource',
      'itemOperations' => [
        'my_op_name' => ['method' => 'GET'],
        'my_other_op_name' => ['method' => 'POST'],
      ],
      'collectionOperations' => [
        'my_collection_op' => ['method' => 'POST', 'path' => 'the/collection/path'],
      ],
      'subresourceOperations' => [
        'my_collection_subresource' => ['path' => 'the/subresource/path'],
      ],
      'graphql' => [
        'query' => [
          'normalization_context' => [
            AbstractNormalizer::GROUPS => ['graphql'],
          ],
        ],
      ],
      'iri' => 'someirischema',
      'attributes' => [
        'normalization_context' => [
          AbstractNormalizer::GROUPS => ['default'],
        ],
        'denormalization_context' => [
          AbstractNormalizer::GROUPS => ['default'],
        ],
        'hydra_context' => [
          '@type' => 'hydra:Operation',
          '@hydra:title' => 'File config Dummy',
        ],
      ],
    ];

    foreach (['shortName', 'description', 'itemOperations', 'collectionOperations', 'subresourceOperations', 'graphql', 'iri', 'attributes'] as $property) {
      $wither = 'with'.ucfirst($property);
      $resourceMetadata = $resourceMetadata->{$wither}($metadata[$property]);
    }

    return [[$resourceMetadata]];
  }


}
