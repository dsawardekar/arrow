<?php

namespace Arrow\Options;

use Encase\Container;

class MockPluginMeta extends \Arrow\PluginMeta {

  public $defaultOptions;

}

class ControllerOptionsValidator extends \Arrow\Options\Validator {

  function loadRules($validator) {
    $validator->rule('required', 'name');
    $validator->rule('safeText', 'name');
    $validator->rule('required', 'email');
    $validator->rule('email', 'email');
  }

}

class ControllerTest extends \WP_UnitTestCase {

  public $container;
  public $pluginMeta;
  public $store;
  public $controller;
  public $printer;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container
      ->object('pluginMeta', new MockPluginMeta('my-plugin.php'))
      ->singleton('optionsStore', 'Arrow\Options\Store')
      ->singleton('ajaxJsonPrinter', 'Arrow\Ajax\JsonPrinter')
      ->singleton('optionsValidator', 'Arrow\Options\ControllerOptionsValidator')
      ->singleton('optionsController', 'Arrow\Options\Controller');

    $this->pluginMeta = $this->container->lookup('pluginMeta');
    $this->store      = $this->container->lookup('optionsStore');
    $this->controller = $this->container->lookup('optionsController');
    $this->printer    = $this->container->lookup('ajaxJsonPrinter');

    $this->pluginMeta->defaultOptions = array(
      'name' => 'your name',
      'email' => 'your@email.com'
    );
  }

  function test_it_has_an_options_store() {
    $this->assertSame($this->store, $this->controller->optionsStore);
  }

  function test_it_can_send_default_plugin_options() {
    $result = $this->controller->all();
    $this->assertEmpty($result);
  }

  function test_it_can_send_default_plugin_options_if_present() {
    $options = array(
      'a' => 1, 'b' => 2
    );

    $meta = $this->getMock('Arrow\PluginMeta', array(), array('foo.php'));
    $meta->expects($this->once())->method('getOptionsContext')->will($this->returnValue($options));

    $this->controller->pluginMeta = $meta;
    $actual = $this->controller->all();

    $this->assertEquals($options, $actual);
  }

  function test_it_can_send_validation_errors_on_update() {
    $this->controller->params = array(
      'name' => '',
      'email' => 'foo'
    );

    $error = $this->controller->patch()->error;

    $this->assertEquals('Name is required', $error['name'][0]);
    $this->assertEquals('Email is not a valid email address', $error['email'][0]);
  }

  function test_it_can_update_plugin_options_for_valid_input() {
    $this->controller->params = array(
      'name' => 'Darshan',
      'email' => 'darshan@email.com',
      'pluginVersion' => '0.0.0'
    );

    $this->controller->patch();
    $data = get_option('my-plugin-options');
    $actual = json_decode($data, true);

    $this->assertEquals(
      $this->controller->params,
      $actual
    );
  }

  function test_it_can_delete_plugin_options() {
    $stored = '{"name":"darshan", "email":"darshan@email.com"}';
    update_option('my-plugin-options', $stored);

    $this->controller->delete();
    $data = get_option('my-plugin-options');

    $this->assertFalse($data);
  }

}
