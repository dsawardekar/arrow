<?php

namespace Arrow\Ajax;

use Encase\Container;

class MockJsonPrinter {

  public $data;
  public $statusCode;

  function sendSuccess($data, $statusCode = '200 OK') {
    $this->data = $data;
    $this->statusCode = $statusCode;
  }

  function sendError($error, $statusCode = '403') {
    $this->data = $error;
    $this->statusCode = $statusCode;
  }

}

class MyAjaxController extends Controller {

  function index() {
    $this->sendSuccess('index');
  }

  function create() {
    $this->sendSuccess('create');
  }

  function update() {
    $this->sendSuccess('update');
  }

  function show() {
    $this->sendSuccess('show');
  }

  function delete() {
    $this->sendSuccess('delete');
  }

  function hello() {
    $this->sendSuccess(
      array('message' => 'Hello ' . $this->params['name'])
    );
  }
}

class AjaxControllerTest extends \WP_UnitTestCase {

  public $printer;
  public $container;
  public $controller;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container
      ->singleton('ajaxJsonPrinter', 'Arrow\Ajax\MockJsonPrinter')
      ->singleton('ajaxController', 'Arrow\Ajax\MyAjaxController');

    $this->printer    = $this->container->lookup('ajaxJsonPrinter');
    $this->controller = $this->container->lookup('ajaxController');
  }

  function test_it_has_a_json_printer() {
    $this->assertSame($this->printer, $this->controller->ajaxJsonPrinter);
  }

  function test_it_does_not_have_any_public_actions() {
    $this->assertEmpty($this->controller->publicActions());
  }

  function test_it_has_rest_admin_actions() {
    $this->assertNotEmpty($this->controller->adminActions());
  }

  function test_it_has_default_capability() {
    $this->assertNotEquals('', $this->controller->capability());
  }

  function test_it_can_send_json_success_data() {
    $this->controller->sendSuccess(array('foo' => 'bar'));
    $this->assertEquals(array('foo' => 'bar'), $this->printer->data);
    $this->assertEquals('200 OK', $this->printer->statusCode);
  }

  function test_it_can_send_json_error_data() {
    $this->controller->sendError(array('foo' => 'bar'));
    $this->assertEquals(array('foo' => 'bar'), $this->printer->data);
    $this->assertEquals('403', $this->printer->statusCode);
  }

  function test_it_can_process_an_action_without_params() {
    $this->controller->process('index');
    $this->assertEquals('index', $this->printer->data);
    $this->assertEquals('200 OK', $this->printer->statusCode);
  }

  function test_it_can_process_invalid_actions() {
    $this->controller->process('foobar');
    $this->assertEquals('invalid_action', $this->printer->data);
    $this->assertEquals('403', $this->printer->statusCode);
  }

  function test_it_can_process_an_action_with_params() {
    $this->controller->process('hello', array('name' => 'Darshan'));
    $this->assertEquals(array('message' => 'Hello Darshan'), $this->printer->data);
    $this->assertEquals('200 OK', $this->printer->statusCode);
  }

}
