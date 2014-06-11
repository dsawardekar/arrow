<?php

namespace Arrow\Ajax;

require_once __DIR__ . '/MyAjaxController.php';
require_once __DIR__ . '/MockJsonPrinter.php';

use Encase\Container;

class MockAjaxSentry {

  public $controller;
  public $action;
  public $params;
  public $authorizeResult;
  public $authorizePublicResult;

  function getController() {
    return $this->controller;
  }

  function getAction() {
    return $this->action;
  }

  function getParams() {
    return $this->params;
  }

  function authorize() {
    return $this->authorizeResult;
  }

  function authorizePublic() {
    return $this->authorizePublicResult;
  }

}

class RouterTest extends \WP_UnitTestCase {

  public $container;
  public $pluginMeta;
  public $router;
  public $sentry;
  public $printer;

  function setUp() {
    parent::setUp();

    $this->pluginMeta = new \Arrow\PluginMeta('my-plugin/my-plugin.php');
    $this->container = new Container();
    $this->container
      ->object('pluginMeta', $this->pluginMeta)
      ->singleton('ajaxJsonPrinter', 'Arrow\Ajax\MockJsonPrinter')
      ->singleton('ajaxSentry', 'Arrow\Ajax\MockAjaxSentry')
      ->singleton('fooController', 'Arrow\Ajax\MyAjaxController')
      ->singleton('ajaxRouter', 'Arrow\Ajax\Router');

    $this->router  = $this->container->lookup('ajaxRouter');
    $this->sentry  = $this->container->lookup('ajaxSentry');
    $this->printer = $this->container->lookup('ajaxJsonPrinter');
  }

  function test_it_has_a_container() {
    $this->assertSame($this->container, $this->router->container);
  }

  function test_it_has_plugin_meta() {
    $this->assertSame($this->pluginMeta, $this->router->pluginMeta);
  }

  function test_it_has_ajax_sentry() {
    $this->assertSame($this->sentry, $this->router->ajaxSentry);
  }

  function test_it_has_admin_hook_name() {
    $this->assertEquals('wp_ajax_my_plugin', $this->router->hookName());
  }

  function test_it_has_public_hook_name() {
    $this->assertEquals('wp_ajax_no_priv_my_plugin', $this->router->hookName(true));
  }

  function test_it_can_register_admin_ajax_hook() {
    $this->router->register();
    $this->assertTrue(has_action('wp_ajax_my_plugin'));
  }

  function test_it_can_register_public_ajax_hook() {
    $this->router->register(true);

    $this->assertTrue(has_action('wp_ajax_my_plugin'));
    $this->assertTrue(has_action('wp_ajax_no_priv_my_plugin'));
  }

  function test_it_will_not_register_if_already_registered() {
    $this->router->register(true);
    $this->router->register();

    $this->assertTrue($this->router->allowPublic);
  }

  function test_it_will_not_reenable_if_already_enabled() {
    $this->router->enable(true);
    $this->router->enable();

    $this->assertTrue($this->router->didEnable);
  }

  function test_it_does_not_process_unauthorized_request() {
    $this->sentry->authorizeResult = false;
    $actual = $this->router->process();

    $this->assertFalse($actual);
  }

  function test_it_does_not_process_unauthorized_public_request() {
    $this->sentry->authorizePublicResult = false;
    $actual = $this->router->processPublic();

    $this->assertFalse($actual);
  }

  function test_it_uses_public_sentry_authorization_if_public() {
    $this->sentry->authorizePublicResult = 'public';
    $actual = $this->router->authorize(true);

    $this->assertEquals('public', $actual);
  }

  function test_it_uses_admin_sentry_authorization_if_not_public() {
    $this->sentry->authorizeResult = 'admin';
    $actual = $this->router->authorize();

    $this->assertEquals('admin', $actual);
  }

  function test_it_can_process_valid_public_ajax_request() {
    $this->sentry->authorizePublicResult = true;
    $this->sentry->controller = 'foo';
    $this->sentry->action = 'index';
    $this->router->processPublic();

    $this->assertEquals('index', $this->printer->data);
    $this->assertEquals(200, $this->printer->statusCode);
  }

  function test_it_can_process_valid_admin_ajax_request() {
    $_GET['admin'] = '1';
    $this->sentry->authorizeResult = true;
    $this->sentry->controller = 'foo';
    $this->sentry->action = 'create';
    $this->router->process();

    $this->assertEquals('create', $this->printer->data);
    $this->assertEquals(200, $this->printer->statusCode);
  }

  /* integration */
  function test_it_can_respond_to_valid_public_ajax_request() {
    $this->sentry->authorizePublicResult = true;
    $this->sentry->controller = 'foo';
    $this->sentry->action = 'index';
    $this->router->register(true);

    do_action('wp_ajax_no_priv_my_plugin');

    $this->assertEquals('index', $this->printer->data);
    $this->assertEquals(200, $this->printer->statusCode);
  }

  function test_it_can_respond_to_valid_admin_ajax_request() {
    $_GET['admin'] = '1';
    $this->sentry->authorizeResult = true;
    $this->sentry->controller = 'foo';
    $this->sentry->action = 'create';
    $this->router->register();

    do_action('wp_ajax_my_plugin');

    $this->assertEquals('create', $this->printer->data);
    $this->assertEquals(200, $this->printer->statusCode);
  }

  function test_it_can_respond_to_valid_public_ajax_request_from_logged_in_user() {
    $_GET['admin'] = '0';
    $this->sentry->authorizeResult = true;
    $this->sentry->controller = 'foo';
    $this->sentry->action = 'index';
    $this->router->register();

    do_action('wp_ajax_my_plugin');

    $this->assertEquals('index', $this->printer->data);
    $this->assertEquals(200, $this->printer->statusCode);
  }

  /* The admin_init hook makes these tests quite slow.
   * The hooks are only supposed to invoke the corresponding register
   * method. So we directly call register here.
   *
   * Should be good enough. Revisit if not working.
   * TODO: figure out why 'admin_init' is slow?
   */
  function test_it_can_be_enabled_for_admin_access() {
    $this->router->enable(false);

    $_GET['admin'] = '1';
    $this->sentry->authorizeResult = true;
    $this->sentry->controller = 'foo';
    $this->sentry->action = 'create';

    //do_action('admin_init');
    $this->router->register(false);
    do_action('wp_ajax_my_plugin');

    $this->assertEquals('create', $this->printer->data);
    $this->assertEquals(200, $this->printer->statusCode);
  }

  function test_it_can_be_enabled_for_public_access() {
    $this->router->enable(true);

    $_GET['admin'] = '0';
    $this->sentry->authorizeResult = true;
    $this->sentry->controller = 'foo';
    $this->sentry->action = 'index';

    //do_action('admin_init');
    $this->router->register(true);
    do_action('wp_ajax_my_plugin');

    $this->assertEquals('index', $this->printer->data);
    $this->assertEquals(200, $this->printer->statusCode);
  }

  /* TODO: integration tests */

}
