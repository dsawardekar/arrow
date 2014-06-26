<?php

namespace Arrow\Ajax;

require_once __DIR__ . '/MockJsonPrinter.php';
require_once __DIR__ . '/MyAjaxController.php';

use Encase\Container;

class ControllerTest extends \WP_UnitTestCase {

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
    $this->container->singleton('ajaxController', 'Arrow\Ajax\Controller');
    $this->controller = $this->container->lookup('ajaxController');
    $this->assertEmpty($this->controller->publicActions());
  }

  function test_it_has_rest_admin_actions() {
    $actual = $this->controller->adminActions();

    $this->assertContains('all', $actual);
    $this->assertContains('get', $actual);
    $this->assertContains('post', $actual);
    $this->assertContains('put', $actual);
    $this->assertContains('patch', $actual);
    $this->assertContains('delete', $actual);
  }

  function test_it_has_valid_action_method_for_all_request() {
    $actual = $this->controller->actionMethods();
    $this->assertEquals(array('GET'), $actual['all']);
  }

  function test_it_has_valid_action_method_for_get_request() {
    $actual = $this->controller->actionMethods();
    $this->assertEquals(array('GET'), $actual['get']);
  }

  function test_it_has_valid_action_method_for_post_request() {
    $actual = $this->controller->actionMethods();
    $this->assertEquals(array('POST'), $actual['post']);
  }

  function test_it_has_valid_action_method_for_put_request() {
    $actual = $this->controller->actionMethods();
    $this->assertEquals(array('PUT', 'POST'), $actual['put']);
  }

  function test_it_has_valid_action_method_for_patch_request() {
    $actual = $this->controller->actionMethods();
    $this->assertEquals(array('PATCH', 'POST'), $actual['patch']);
  }

  function test_it_has_valid_action_method_for_delete_request() {
    $actual = $this->controller->actionMethods();
    $this->assertEquals(array('DELETE', 'GET', 'POST'), $actual['delete']);
  }

  function test_it_has_default_capability() {
    $this->assertNotEquals('', $this->controller->capability());
  }

  function test_it_can_send_json_success_data() {
    $this->controller->sendSuccess(array('foo' => 'bar'));
    $this->assertEquals(array('foo' => 'bar'), $this->printer->data);
    $this->assertEquals(200, $this->printer->statusCode);
  }

  function test_it_can_send_json_error_data() {
    $this->controller->sendError(array('foo' => 'bar'));
    $this->assertEquals(array('foo' => 'bar'), $this->printer->data);
    $this->assertEquals(403, $this->printer->statusCode);
  }

  function test_it_can_process_an_action_without_params() {
    $this->controller->process('get');
    $this->assertEquals('get', $this->printer->data);
    $this->assertEquals(200, $this->printer->statusCode);
  }

  function test_it_can_process_invalid_actions() {
    $this->controller->process('foobar');
    $this->assertEquals('invalid_action', $this->printer->data);
    $this->assertEquals(403, $this->printer->statusCode);
  }

  function test_it_can_process_an_action_with_params() {
    $this->controller->process('hello', array('name' => 'Darshan'));
    $this->assertEquals(array('message' => 'Hello Darshan'), $this->printer->data);
    $this->assertEquals(200, $this->printer->statusCode);
  }

  function test_it_has_a_validator() {
    $this->controller->params = array('foo' => 1);
    $validator = $this->controller->getValidator();
    $validator->rule('required', 'foo');

    $this->assertTrue($validator->validate());
  }

  function test_it_returns_an_error_object_as_error_result() {
    $result = $this->controller->error('foo', 422);

    $this->assertInstanceOf('Arrow\Ajax\ControllerError', $result);
    $this->assertEquals('foo', $result->error);
    $this->assertEquals(422, $result->statusCode);
  }

  function test_it_sends_success_for_result_returning_action() {
    $this->controller->doAction('get');
    $this->assertEquals('get', $this->printer->data);
    $this->assertEquals(200, $this->printer->statusCode);
  }

  function test_it_sends_error_for_error_returning_action() {
    $this->controller->doAction('helloError');
    $this->assertEquals('helloError', $this->printer->data);
    $this->assertEquals(403, $this->printer->statusCode);
  }

  function test_it_prints_send_success_argument_if_called_ignoring_returned_value_of_action() {
    $this->controller->params = array('name' => 'Darshan');
    $this->controller->doAction('hello');

    $this->assertEquals(array('message' => 'Hello Darshan'), $this->printer->data);
  }

  function test_it_prints_send_error_argument_if_called_ignoring_returned_value_of_action() {
    $this->controller->doAction('helloError');
    $this->assertEquals('helloError', $this->printer->data);
  }

  function test_it_sends_true_result_of_action_to_printer() {
    $this->controller->doAction('doTrue');
    $this->assertTrue($this->printer->data);
  }

  function test_it_sends_false_result_of_action_to_printer() {
    $this->controller->doAction('doFalse');
    $this->assertFalse($this->printer->data);
  }

  function test_it_trap_and_sends_exception_inside_actions() {
    $this->controller->process('helloException');
    $this->assertEquals('helloException', $this->printer->data);
    $this->assertEquals(500, $this->printer->statusCode);
  }

}
