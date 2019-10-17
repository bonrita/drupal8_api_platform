<?php


namespace Drupal\Tests\api_platform\Hydra\Serializer;

use Drupal\api_platform\Core\Api\Entrypoint;
use Drupal\api_platform\Core\Api\IriConverterInterface;
use Drupal\api_platform\Core\Api\UrlGeneratorInterface;
use Drupal\api_platform\Core\Hydra\Serializer\EntrypointNormalizer;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\ResourceNameCollection;
use Drupal\Tests\UnitTestCase;

/**
 * Class EntrypointNormalizerTest
 *
 * @package Drupal\Tests\api_platform\Hydra\Serializer
 *
 * @coversDefaultClass \Drupal\api_platform\Core\Hydra\Serializer\EntrypointNormalizer
 */
class EntrypointNormalizerTest extends UnitTestCase {

  public function testSupportNormalization() {
    $collection = new ResourceNameCollection();
    $entrypoint = new Entrypoint($collection);

    $factoryProphecy = $this->prophesize(ResourceMetadataFactoryInterface::class);
    $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
    $urlGeneratorProphecy = $this->prophesize(UrlGeneratorInterface::class);

    $normalizer = new EntrypointNormalizer($factoryProphecy->reveal(), $iriConverterProphecy->reveal(), $urlGeneratorProphecy->reveal());

    $this->assertTrue($normalizer->supportsNormalization($entrypoint, EntrypointNormalizer::FORMAT));
    $this->assertFalse($normalizer->supportsNormalization(new \stdClass(), EntrypointNormalizer::FORMAT));
    $this->assertFalse($normalizer->supportsNormalization($entrypoint, 'json'));
  }

}
