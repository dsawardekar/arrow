<?php

namespace Arrow;

use Encase\Container;

class SentryTest extends \WP_UnitTestCase {

  public $container;
  public $pluginMeta;
  public $sentry;

  function setUp() {
    parent::setUp();

    $this->pluginMeta = new PluginMeta('my-sentry.php');
    $this->container = new Container();
    $this->container
      ->object('pluginMeta', $this->pluginMeta)
      ->singleton('sentry', 'Arrow\Sentry');

    $this->sentry = $this->container->lookup('sentry');
  }

  function test_it_has_a_container() {
    $this->assertSame($this->container, $this->sentry->container);
  }

  function test_it_knows_if_running_in_php_unit() {
    $this->assertTrue($this->sentry->isPHPUnit());
  }

  function test_it_stores_valid_method() {
    $this->sentry->setValidMethod('POST');
    $this->assertEquals('POST', $this->sentry->getValidMethod());
  }

  function test_it_has_request_method_if_present() {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $this->assertEquals('POST', $this->sentry->getMethod());
  }

  function test_it_has_empty_request_method_if_absent() {
    $this->assertEquals('', $this->sentry->getMethod());
  }

  function test_it_knows_if_valid_request_method() {
    $this->sentry->setValidMethod('POST');
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $this->assertTrue($this->sentry->isValidMethod());
  }

  function test_it_knows_if_not_valid_request_method() {
    $this->sentry->setValidMethod('POST');
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $this->assertFalse($this->sentry->isValidMethod());
  }

  function test_it_can_store_referer() {
    $this->sentry->setValidReferer('foo');
    $this->assertEquals('foo', $this->sentry->getValidReferer());
  }

  function test_it_knows_current_referer_if_present() {
    $_SERVER['HTTP_REFERER'] = 'foo';
    $this->assertEquals('foo', $this->sentry->getReferer());
  }

  function test_it_has_empty_referer_if_absent() {
    $this->assertEquals('', $this->sentry->getReferer());
  }

  function test_it_knows_if_referer_is_not_valid() {
    $this->assertFalse($this->sentry->isValidReferer());
  }

  function test_it_knows_if_referer_is_valid() {
    $this->sentry->setValidReferer('foo');
    $_SERVER['HTTP_REFERER'] = 'foo';

    $this->assertTrue($this->sentry->isValidReferer());
  }

  function test_it_stores_nonce_name() {
    $this->sentry->setNonceName('foo');
    $this->assertEquals('foo', $this->sentry->getNonceName());
  }

  function test_it_has_nonce_if_present() {
    $_GET['nonce'] = 'foo';
    $this->assertEquals('foo', $this->sentry->getNonceValue());
  }

  function test_it_has_empty_nonce_if_absent() {
    $this->assertEquals('', $this->sentry->getNonceValue());
  }

  function test_it_knows_if_nonce_is_valid() {
    $this->sentry->setNonceName('my-plugin');
    $nonce = wp_create_nonce('my-plugin');
    $_GET['nonce'] = $nonce;

    $this->assertTrue($this->sentry->isValidNonce());
  }

  function test_it_knows_if_nonce_is_invalid() {
    $this->sentry->setNonceName('my-plugin');
    $_GET['nonce'] = 'foo';

    $this->assertFalse($this->sentry->isValidNonce());
  }

  function test_it_stores_valid_logged_in() {
    $this->sentry->setValidLoggedIn(true);
    $this->assertTrue($this->sentry->getValidLoggedIn());
  }

  function test_it_knows_if_user_is_not_logged_in() {
    $this->sentry->setValidLoggedIn(true);
    $this->assertFalse($this->sentry->isValidLoggedIn());
  }

  function test_it_knows_if_user_is_logged_in() {
    wp_set_current_user(1);
    $this->assertTrue($this->sentry->isValidLoggedIn());
  }

  function test_it_does_not_need_to_be_logged_in_if_not_required() {
    $this->sentry->setValidLoggedIn(false);
    $this->assertTrue($this->sentry->isValidLoggedIn());
  }

  function test_it_stores_valid_permissions() {
    $this->sentry->setValidPermissions('foo');
    $this->assertEquals('foo', $this->sentry->getValidPermissions());
  }

  function test_it_knows_if_user_does_not_have_valid_permissions() {
    $this->assertFalse($this->sentry->hasValidPermissions());
  }

  function test_it_knows_if_user_has_valid_permissions() {
    wp_set_current_user(1);
    $this->sentry->setValidPermissions('manage_options');
    $this->assertTrue($this->sentry->hasValidPermissions());
  }

  function test_it_can_quit_safely() {
    $this->sentry->quit();
    $this->assertTrue($this->sentry->didQuit);
  }

  function test_it_can_deny_request() {
    $this->sentry->deny('foo');
    $this->assertEquals('foo', $this->sentry->denyReason);
    $this->assertTrue($this->sentry->didDeny);
    $this->assertTrue($this->sentry->didQuit);
  }

  /* integration tests */
  function test_it_denies_request_without_matching_request_method() {
    $this->sentry->setValidMethod('POST');
    $_SERVER['REQUEST_METHOD'] = 'GET';

    $this->assertFalse($this->sentry->authorize());
    $this->assertEquals('invalid_method', $this->sentry->denyReason);
  }

  function test_it_denies_request_without_valid_referer() {
    $this->sentry->setValidMethod('GET');
    $_SERVER['REQUEST_METHOD'] = 'GET';

    $this->sentry->setValidReferer('foo');
    $_SERVER['HTTP_REFERER'] = 'bar';

    $this->assertFalse($this->sentry->authorize());
    $this->assertEquals('invalid_referer', $this->sentry->denyReason);
  }

  function test_it_denies_request_without_valid_nonce() {
    $this->sentry->setValidMethod('GET');
    $this->sentry->setValidReferer('admin-ajax.php');
    $this->sentry->setNonceName('my-plugin');

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['HTTP_REFERER'] = 'admin-ajax.php';
    $_GET['nonce'] = '1234';

    $this->assertFalse($this->sentry->authorize());
    $this->assertEquals('invalid_nonce', $this->sentry->denyReason);
  }

  function test_it_denies_request_for_not_logged_in_user() {
    $this->sentry->setValidMethod('POST');
    $this->sentry->setValidReferer('admin-ajax.php');
    $this->sentry->setNonceName('my-plugin');
    $this->sentry->setValidLoggedIn(true);

    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_REFERER']   = 'admin-ajax.php';
    $_GET['nonce']            = wp_create_nonce('my-plugin');

    $this->assertFalse($this->sentry->authorize());
    $this->assertEquals('not_logged_in', $this->sentry->denyReason);
  }

  function test_it_denies_request_for_user_without_permissions() {
    $id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
    wp_set_current_user($id);

    $this->sentry->setValidMethod('POST');
    $this->sentry->setValidReferer('admin-ajax.php');
    $this->sentry->setNonceName('my-plugin');
    $this->sentry->setValidLoggedIn(true);
    $this->sentry->setValidPermissions('manage_options');

    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_REFERER']   = 'admin-ajax.php';
    $_GET['nonce']            = wp_create_nonce('my-plugin');

    $this->assertFalse($this->sentry->authorize());
    $this->assertEquals('invalid_permissions', $this->sentry->denyReason);
  }

  function test_it_authorizes_valid_request() {
    wp_set_current_user(1);

    $this->sentry->setValidMethod('POST');
    $this->sentry->setValidReferer('admin-ajax.php');
    $this->sentry->setNonceName('my-plugin');
    $this->sentry->setValidLoggedIn(true);
    $this->sentry->setValidPermissions('manage_options');

    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_REFERER']   = 'admin-ajax.php';
    $_GET['nonce']            = wp_create_nonce('my-plugin');

    $this->assertTrue($this->sentry->authorize());
  }

}
