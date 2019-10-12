<?php

declare(strict_types=1);

namespace Drupal\Tests\api_platform\Action;


use Drupal\api_platform\Core\Action\SwaggerUiAction;
use Drupal\api_platform\Core\Documentation\Documentation;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\ResourceNameCollection;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Routing\Router;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class SwaggerUiActionTest
 *
 * @package Drupal\Tests\api_platform\Action
 * @coversDefaultClass \Drupal\api_platform\Core\Action\SwaggerUiAction
 */
class SwaggerUiActionTest extends UnitTestCase {

  public const SPEC = [
    'paths' => [
      '/fs' => ['get' => ['operationId' => 'getFCollection']],
      '/fs/{id}' => ['get' => ['operationId' => 'getFItem']],
    ],
  ];

  /**
   * @dataProvider getInvokeParameters
   */
  public function testInvoke(Request $request, $rendererCollectionProphecy) {
    $resourceNameCollectionFactoryProphecy = $this->prophesize(ResourceNameCollectionFactoryInterface::class);
    $resourceNameCollectionFactoryProphecy->create()->willReturn(new ResourceNameCollection(['Foo', 'Bar']))->shouldBeCalled();

    $resourceMetadataFactoryProphecy = $this->prophesize(ResourceMetadataFactoryInterface::class);

    $normalizerProphecy = $this->prophesize(NormalizerInterface::class);
    $normalizerProphecy->normalize(Argument::type(Documentation::class), 'json', Argument::type('array'))->willReturn(self::SPEC)->shouldBeCalled();

    $urlGeneratorProphecy = $this->prophesize(Router::class);

    $action = new SwaggerUiAction(
      $resourceNameCollectionFactoryProphecy->reveal(),
      $resourceMetadataFactoryProphecy->reveal(),
      $normalizerProphecy->reveal(),
      $rendererCollectionProphecy->reveal(),
      $urlGeneratorProphecy->reveal()
    );

    $this->assertInstanceOf(Response::class, $action($request));

  }

  public function getInvokeParameters()
  {
    $element = [
      'title' => '',
      'description' => '',
      'formats' => [],
      'swaggerUiEnabled' => false,
      'showWebby' => true,
      'reDocEnabled' => false,
      'graphqlEnabled' => false,
      'swagger_data' => [
        'url' => '/url',
        'spec' => self::SPEC,
        'oauth' => [
          'enabled' => false,
          'clientId' => '',
          'clientSecret' => '',
          'type' => '',
          'flow' => '',
          'tokenUrl' => '',
          'authorizationUrl' => '',
          'scopes' => [],
        ],
        'shortName' => 'F',
        'operationId' => 'getFItem',
        'id' => null,
        'queryParameters' => [],
        'path' => '/fs/{id}',
        'method' => 'get',
    ]
    ];
    $rendererCollectionProphecy = $this->prophesize(Renderer::class);
    $rendererCollectionProphecy->executeInRenderContext(Argument::type(RenderContext::class), Argument::type('callable'))->shouldBeCalled();
    $rendererCollectionProphecy->render($element)->shouldBeCalled();

    return [
      [new Request([], [], ['_api_resource_class' => 'Foo', '_api_collection_operation_name' => 'get']), $rendererCollectionProphecy],
      [new Request([], [], ['_api_resource_class' => 'Foo', '_api_item_operation_name' => 'get']), $rendererCollectionProphecy],
      [new Request([], [], ['_api_resource_class' => 'Foo', '_api_item_operation_name' => 'get'], [], [], ['REQUEST_URI' => '/docs', 'SCRIPT_FILENAME' => '/docs']), $rendererCollectionProphecy],
    ];
  }

}
