<?php

namespace Arrow\Ember;

use Encase\Container;

class MyOptionsValidator extends \Arrow\OptionsManager\OptionsValidator {

  function loadRules($validator) {
    $validator->rule('required', 'name');
    $validator->rule('safeText', 'name');
    $validator->rule('required', 'email');
    $validator->rule('email', 'email');
  }

}

class OptionsManagerTest extends \WP_UnitTestCase {

  public $container;
  public $controller;
  public $store;
  public $validator;
  public $manager;
  public $pluginMeta;
  public $router;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container
      ->object('pluginMeta', new \Arrow\PluginMeta('my-plugin.php'))
      ->object('optionsManager', new \Arrow\Ember\OptionsManager($this->container))
      ->singleton('optionsStore', 'Arrow\OptionsManager\OptionsStore');

    $this->pluginMeta = $this->container->lookup('pluginMeta');
    $this->manager    = $this->container->lookup('optionsManager');
    $this->controller = $this->container->lookup('optionsController');
    $this->store      = $this->container->lookup('optionsStore');
  }

  function test_it_stores_default_options_store() {
    $this->assertInstanceOf(
      'Arrow\OptionsManager\OptionsStore',
      $this->container->lookup('optionsStore')
    );
  }

  function test_it_stores_default_ajax_sentry() {
    $this->assertInstanceOf(
      'Arrow\Ajax\Sentry',
      $this->container->lookup('ajaxSentry')
    );
  }

  function test_it_stores_default_ajax_json_printer() {
    $this->assertInstanceOf(
      'Arrow\Ajax\JsonPrinter',
      $this->container->lookup('ajaxJsonPrinter')
    );
  }

  function test_it_stores_default_options_page() {
    $this->assertInstanceOf(
      'Arrow\Ember\OptionsPage',
      $this->container->lookup('optionsPage')
    );
  }

  function test_it_stores_default_options_controller() {
    $this->assertInstanceOf(
      'Arrow\Ember\OptionsController',
      $this->container->lookup('optionsController')
    );
  }

  function test_it_stores_default_ajax_router() {
    $this->assertInstanceOf(
      'Arrow\Ajax\Router',
      $this->container->lookup('ajaxRouter')
    );
  }

  function test_it_can_lookup_container_items() {
    $this->assertSame($this->pluginMeta, $this->manager->lookup('pluginMeta'));
  }

  function test_it_can_register_options_page() {
    $this->manager->onAdminMenu();
    // TODO
  }

  function test_it_has_new_nonce_value() {
    $nonce = $this->manager->getNonceValue();
    $this->assertEquals(1, wp_verify_nonce($nonce, $this->pluginMeta->getSlug()));
  }

  function test_it_has_api_endpoint() {
    $apiEndpoint = $this->manager->getApiEndpoint();
    $this->assertContains('admin-ajax.php', $apiEndpoint);
    $this->assertContains('action=my-plugin', $apiEndpoint);
    $this->assertContains('admin=1', $apiEndpoint);
  }

  function test_it_has_ember_config() {
    $config      = $this->manager->getEmberConfig(null);
    $apiEndpoint = $config['apiEndpoint'];
    $nonce       = $config['nonce'];

    $this->assertEquals(1, wp_verify_nonce($nonce, $this->pluginMeta->getSlug()));
    $this->assertContains('admin-ajax.php', $apiEndpoint);
    $this->assertContains('action=my-plugin', $apiEndpoint);
    $this->assertContains('admin=1', $apiEndpoint);
  }

  function test_it_has_app_slug() {
    $actual = $this->manager->getEmberAppSlug();
    $this->assertEquals('my-plugin-app', $actual);
  }

  function test_it_can_load_ember() {
    $this->manager->loadEmber();
    $loader = $this->container->lookup('adminScriptLoader');

    $this->assertTrue($loader->isScheduled('handlebars'));
    $this->assertTrue($loader->isScheduled('parsley'));
    $this->assertTrue($loader->isScheduled('ember'));
    $this->assertTrue($loader->isScheduled('my-plugin-app'));
  }

  function test_it_can_load_app_styles() {
    $this->manager->loadStyles();
    $loader = $this->container->lookup('adminStylesheetLoader');

    $this->assertTrue($loader->isScheduled('parsley'));
    $this->assertTrue($loader->isScheduled('my-plugin-app'));
  }

  function test_it_does_not_allow_public_access_to_api_endpoint() {
    $this->assertFalse($this->manager->getAllowPublic());
  }

  function test_it_can_register_ajax_router() {
    $this->manager->onAdminInit();
  }
}
