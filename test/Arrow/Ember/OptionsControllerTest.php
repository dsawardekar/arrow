<?php

namespace Arrow\Ember;

use Encase\Container;

class MockPluginMeta extends \Arrow\PluginMeta {

  public $defaultOptions;

}

class EmberOptionsValidator extends \Arrow\OptionsManager\OptionsValidator {

  function loadRules($validator) {
    $validator->rule('required', 'name');
    $validator->rule('safeText', 'name');
    $validator->rule('required', 'email');
    $validator->rule('email', 'email');
  }

}

class OptionsControllerTest extends \WP_UnitTestCase {

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
      ->singleton('optionsStore', 'Arrow\OptionsManager\OptionsStore')
      ->singleton('ajaxJsonPrinter', 'Arrow\Ajax\JsonPrinter')
      ->singleton('optionsValidator', 'Arrow\Ember\EmberOptionsValidator')
      ->singleton('optionsController', 'Arrow\Ember\OptionsController');

    $this->pluginMeta = $this->container->lookup('pluginMeta');
    $this->store      = $this->container->lookup('optionsStore');
    $this->controller = $this->container->lookup('optionsController');
    $this->printer    = $this->container->lookup('ajaxJsonPrinter');

    $this->pluginMeta->defaultOptions = array(
      'name' => 'your name',
      'email' => 'your@email.com'
    );

    \Arrow\OptionsManager\CustomValitronRules::load();
  }

  function test_it_has_an_options_store() {
    $this->assertSame($this->store, $this->controller->optionsStore);
  }

  function test_it_can_send_default_plugin_options() {
    ob_start();
    $this->controller->index();
    $response = ob_get_clean();

    $json = json_decode($response, true);
    $this->assertEquals(200, $json['status']);
    $this->assertTrue($json['success']);
    $this->assertEquals(
      array('name' => 'your name', 'email' => 'your@email.com'),
      $json['data']
    );
  }

  function test_it_can_send_stored_plugin_options() {
    $stored = '{"name":"darshan", "email":"darshan@email.com"}';
    update_option('my-plugin-options', $stored);

    ob_start();
    $this->controller->index();
    $response = ob_get_clean();

    $json = json_decode($response, true);
    $this->assertEquals(200, $json['status']);
    $this->assertTrue($json['success']);
    $this->assertEquals(
      array('name' => 'darshan', 'email' => 'darshan@email.com'),
      $json['data']
    );
  }

  function test_it_can_send_validation_errors_on_update() {
    $this->controller->params = array(
      'name' => '',
      'email' => 'foo'
    );

    ob_start();
    $this->controller->update();
    $response = ob_get_clean();

    $json = json_decode($response, true);
    $error = $json['data']['error'];
    $this->assertEquals('Name is required', $error['name'][0]);
    $this->assertEquals('Email is not a valid email address', $error['email'][0]);
    $this->assertEquals(422, $json['status']);
    $this->assertFalse($json['success']);
  }

  function test_it_can_update_plugin_options_for_valid_input() {
    $this->controller->params = array(
      'name' => 'Darshan',
      'email' => 'darshan@email.com'
    );

    ob_start();
    $this->controller->update();
    $response = ob_get_clean();
    $json     = json_decode($response, true);

    $this->assertTrue($json['success']);
    $this->assertEquals(200, $json['status']);
    $this->assertEquals(
      $this->controller->params,
      $json['data']
    );
  }

  function test_it_can_delete_plugin_options() {
    $stored = '{"name":"darshan", "email":"darshan@email.com"}';
    update_option('my-plugin-options', $stored);

    ob_start();
    $this->controller->delete();
    $response = ob_get_clean();

    $json = json_decode($response, true);
    $this->assertEquals($this->pluginMeta->defaultOptions, $json['data']);
  }

}
