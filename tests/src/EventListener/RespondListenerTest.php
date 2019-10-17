<?php


namespace Drupal\Tests\api_platform\EventListener;


use Drupal\api_platform\Core\EventListener\RespondListener;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class RespondListenerTest
 *
 * @package Drupal\Tests\api_platform\EventListener
 * @coversDefaultClass \Drupal\api_platform\Core\EventListener\RespondListener
 */
class RespondListenerTest extends UnitTestCase {

  public function testDoNotHandleResponse() {

    $eventProphecy = $this->prophesize(
      GetResponseForControllerResultEvent::class
    );
    $eventProphecy->getControllerResult()->willReturn(new Response());
    $eventProphecy->getRequest()->willReturn(new Request());
    $eventProphecy->setResponse(Argument::any())->shouldNotBeCalled();

    $listener = new RespondListener();
    $listener->onKernelView($eventProphecy->reveal());
  }

  public function testDoNotHandleWhenRespondFlagIsFalse() {
    $request = new Request([], [], ['_api_respond' => FALSE]);

    $eventProphecy = $this->prophesize(
      GetResponseForControllerResultEvent::class
    );
    $eventProphecy->getControllerResult()->willReturn('foo');
    $eventProphecy->getRequest()->willReturn($request);
    $eventProphecy->setResponse(Argument::any())->shouldNotBeCalled();

    $listener = new RespondListener();
    $listener->onKernelView($eventProphecy->reveal());
  }

  public function testCreate200Response() {
    $request = new Request([], [], ['_api_respond' => TRUE]);
    $request->setRequestFormat('xml');

    $httpKernel = $this->prophesize(HttpKernelInterface::class);
    $event = new GetResponseForControllerResultEvent(
      $httpKernel->reveal(),
      $request,
      HttpKernelInterface::MASTER_REQUEST,
      'foo'
    );

    (new RespondListener())->onKernelView($event);

    $response = $event->getResponse();

    $this->assertEquals('foo', $response->getContent());
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    $this->assertEquals('text/xml; charset=utf-8', $response->headers->get('Content-Type'));
    $this->assertEquals('Accept', $response->headers->get('Vary'));
    $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
    $this->assertEquals('deny', $response->headers->get('X-Frame-Options'));
  }


}
