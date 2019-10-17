<?php


namespace Drupal\Tests\api_platform\EventListener;


use Composer\Autoload\ClassLoader;
use Drupal\api_platform\Core\EventListener\SerializeListener;
use Drupal\api_platform\Core\Serializer\SerializerContextBuilderInterface;
use Drupal\Tests\api_platform\Fixtures\Entity\Dummy;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class SerializeListenerTest
 *
 * @package Drupal\Tests\api_platform\EventListener
 * @coversDefaultClass \Drupal\api_platform\Core\EventListener\SerializeListener
 */
class SerializeListenerTest extends UnitTestCase {

  protected $loader;



  protected function setUp() {
    parent::setUp();

    $this->loader = new ClassLoader();
    $this->loader->addPsr4("Drupal\\Tests\\api_platform\\Fixtures\\", __DIR__.'/../fixtures');
    $this->loader->register(TRUE);

  }

  protected function tearDown() {
    parent::tearDown();

    $this->loader->unregister();
    $this->loader = null;
  }

  public function testDoNotSerializeWhenControllerResultIsResponse() {
    $serializerProphecy = $this->prophesize(SerializerInterface::class);
    $serializerProphecy->serialize(Argument::cetera())->shouldNotBeCalled();

    $eventProphecy = $this->prophesize(GetResponseForControllerResultEvent::class);
    $eventProphecy->getControllerResult()->willReturn(new Response());
    $eventProphecy->getRequest()->willReturn(new Request());

    $serializerContextBuilderProphecy = $this->prophesize(SerializerContextBuilderInterface::class);

    $listener = new SerializeListener($serializerProphecy->reveal(), $serializerContextBuilderProphecy->reveal());
    $listener->onKernelView($eventProphecy->reveal());

  }

  public function testDoNotSerializeWhenRespondFlagIsFalse()
  {
    $serializerProphecy = $this->prophesize(SerializerInterface::class);
    $serializerProphecy->serialize(Argument::cetera())->shouldNotBeCalled();

    $serializerContextBuilderProphecy = $this->prophesize(SerializerContextBuilderInterface::class);

    $dummy = new Dummy();

    $request = new Request([], [], ['data' => $dummy, '_api_resource_class' => Dummy::class, '_api_collection_operation_name' => 'post', '_api_respond' => false]);
    $request->setMethod('POST');

    $eventProphecy = $this->prophesize(GetResponseForControllerResultEvent::class);
    $eventProphecy->getControllerResult()->willReturn($dummy);
    $eventProphecy->getRequest()->willReturn($request);

    $listener = new SerializeListener($serializerProphecy->reveal(), $serializerContextBuilderProphecy->reveal());
    $listener->onKernelView($eventProphecy->reveal());
  }

}
