<?php

namespace Arrow\OptionsManager;

require_once __DIR__ . '/PluginMeta.php';
use Encase\Container;

class OptionsPostHandlerTest extends \WP_UnitTestCase {

  public $handler;
  public $pluginMeta;
  public $container;
  public $flash;
  public $validator;

  function setUp() {
    parent::setUp();

    $this->pluginMeta = new PluginMeta();
    $this->pluginMeta->optionsKey = 'post-handler-plugin-options';
    $this->pluginMeta->defaultOptions = array('foo' => 'bar');
    $this->pluginMeta->optionsCapability = 'manage_options';
    $this->pluginMeta->slug = 'post-handler-plugin';

    $this->container = new Container();
    $this->container
      ->object('pluginMeta', $this->pluginMeta)
      ->singleton('optionsFlash', 'Arrow\OptionsManager\OptionsFlash')
      ->singleton('optionsValidator', 'Arrow\OptionsManager\MyOptionsValidator')
      ->singleton('optionsStore', 'Arrow\OptionsManager\OptionsStore')
      ->singleton('handler', 'Arrow\OptionsManager\OptionsPostHandler');

    $this->handler   = $this->container->lookup('handler');
    $this->flash     = $this->container->lookup('optionsFlash');
    $this->validator = $this->container->lookup('optionsValidator');
  }

  function tearDown() {
    //$this->flash->clear();
  }

  function test_it_has_plugin_meta() {
    $this->assertSame($this->pluginMeta, $this->handler->pluginMeta);
  }

  function test_it_has_a_post_action() {
    $actual = $this->handler->getPostAction();
    $this->assertEquals('post_handler_plugin_options_post', $actual);
  }

  function test_it_can_setup_admin_post_handler() {
    $this->handler->enable();
    $this->assertTrue(has_action('admin_post_' . $this->handler->getPostAction()));
  }

  function test_it_has_nonce_name() {
    $actual = $this->handler->getNonceName();
    $this->assertEquals('post_handler_plugin_options_post_wpnonce', $actual);
  }

  function test_it_knows_if_request_is_not_a_POST() {
    $this->assertFalse($this->handler->isPOST());
  }

  function test_it_knows_if_request_is_a_POST() {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $this->assertTrue($this->handler->isPOST());
  }

  function test_it_can_find_nonce_in_POST() {
    $_POST[$this->handler->getNonceName()] = 'foo';
    $this->assertEquals('foo', $this->handler->getNonceValue());
  }

  function test_it_knows_if_nonce_is_not_valid() {
    $nonce = 'foo';
    $this->assertFalse($this->handler->isValidNonce());
  }

  function test_it_knows_if_nonce_is_valid() {
    $nonce = wp_create_nonce($this->handler->getPostAction());
    $_POST[$this->handler->getNonceName()] = $nonce;
    $this->assertTrue($this->handler->isValidNonce());
  }

  function test_it_knows_if_user_is_not_logged_in() {
    $this->assertFalse($this->handler->isLoggedIn());
  }

  function test_it_knows_if_user_is_logged_in() {
    wp_set_current_user(1);
    $this->assertTrue($this->handler->isLoggedIn());
  }

  function test_it_knows_if_user_can_manage_options() {
    wp_set_current_user(1);
    $this->assertTrue($this->handler->hasOptionsAccess());
  }

  function test_it_knows_if_user_can_not_manage_options() {
    $this->assertFalse($this->handler->hasOptionsAccess());
  }

  function test_it_knows_if_request_was_denied() {
    $this->handler->deny();
    $this->assertTrue($this->handler->didDeny);
  }

  function test_it_knows_if_request_was_not_denied() {
    $this->assertFalse($this->handler->didDeny);
  }

  function test_it_knows_if_request_did_quit() {
    $this->handler->quit();
    $this->assertTrue($this->handler->didQuit);
  }

  function test_it_knows_if_request_did_not_quit() {
    $this->assertFalse($this->handler->didQuit);
  }

  function test_it_can_save_success_flash() {
    $this->handler->saveSuccess();
    $value = $this->flash->getValue();

    $this->assertTrue($value['success']);
  }

  function test_it_can_save_errors_flash() {
    $errors = array('name' => array('name is required'));
    $this->handler->saveErrors($errors);
    $value = $this->flash->getValue();

    $this->assertEquals('name is required', $value['errors']['name'][0]);
  }

  function test_it_can_redirect_to_options_url() {
    $optionsUrl = $this->pluginMeta->getOptionsUrl();
    $this->handler->redirect();

    $this->assertEquals($optionsUrl, $this->handler->redirectTo);
    $this->assertTrue($this->handler->didQuit);
  }

  function test_it_can_validate_valid_user_input() {
    $rules = array(
        'required' => array(
            array('foo'),
            array('bar')
        ),
        'length' => array(
            array('foo', 3)
        )
    );

    $_POST['foo'] = 'bar';
    $_POST['bar'] = 'two';

    $this->validator->rules = $rules;
    $this->handler->validate();

    $value = $this->flash->getValue();

    $this->assertTrue($value['success']);
  }

  function test_it_can_validate_invalid_user_input() {
    $rules = array(
        'required' => array(
            array('foo'),
            array('bar')
        ),
        'length' => array(
            array('foo', 3)
        )
    );

    $_POST['foo'] = 'a';

    $this->validator->rules = $rules;
    $this->handler->validate();

    $value = $this->flash->getValue();

    $this->assertEquals(2, count($value['errors']));
  }

  function test_it_can_get_current_referer() {
    $_SERVER['HTTP_REFERER'] = 'foo';
    $this->assertEquals('foo', $this->handler->getReferer());
  }

  function test_it_returns_empty_referer_if_not_found() {
    $this->assertEquals('', $this->handler->getReferer());
  }

  function test_it_knows_if_referer_is_invalid() {
    $this->assertFalse($this->handler->isValidReferer());
  }

  function test_it_knows_if_referer_is_valid() {
    $_SERVER['HTTP_REFERER'] = $this->pluginMeta->getOptionsUrl();
    $this->assertTrue($this->handler->isValidReferer());
  }

  /* integration tests */

  function test_it_denies_a_GET_request() {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $this->handler->enable();
    do_action('admin_post_' . $this->handler->getPostAction());

    $this->assertTrue($this->handler->didDeny);
    $this->assertEquals('not_post', $this->handler->denyReason);
  }

  function test_it_denies_a_request_with_an_invalid_referer() {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_REFERER'] = 'foo';
    $this->handler->enable();
    do_action('admin_post_' . $this->handler->getPostAction());

    $this->assertTrue($this->handler->didDeny);
    $this->assertEquals('invalid_referer', $this->handler->denyReason);
  }

  function test_it_denies_a_request_without_a_referer() {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $this->handler->enable();
    do_action('admin_post_' . $this->handler->getPostAction());

    $this->assertTrue($this->handler->didDeny);
    $this->assertEquals('invalid_referer', $this->handler->denyReason);
  }

  function test_it_denies_a_request_without_a_nonce() {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_REFERER'] = $this->pluginMeta->getOptionsUrl();
    $this->handler->enable();
    do_action('admin_post_' . $this->handler->getPostAction());

    $this->assertTrue($this->handler->didDeny);
    $this->assertEquals('invalid_nonce', $this->handler->denyReason);
  }

  function test_it_denies_a_request_if_invalid_nonce() {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_REFERER'] = $this->pluginMeta->getOptionsUrl();
    $_POST[$this->handler->getNonceName()] = 'foo';
    $this->handler->enable();
    do_action('admin_post_' . $this->handler->getPostAction());

    $this->assertTrue($this->handler->didDeny);
    $this->assertEquals('invalid_nonce', $this->handler->denyReason);
  }

  function test_it_denies_a_request_if_not_logged_in() {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_REFERER'] = $this->pluginMeta->getOptionsUrl();
    $_POST[$this->handler->getNonceName()] = wp_create_nonce($this->handler->getPostAction());
    $this->handler->enable();
    do_action('admin_post_' . $this->handler->getPostAction());

    $this->assertTrue($this->handler->didDeny);
    $this->assertEquals('not_logged_in', $this->handler->denyReason);
  }

  function test_it_denies_a_request_if_not_enough_permissions() {
    $id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
    wp_set_current_user($id);

    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_REFERER'] = $this->pluginMeta->getOptionsUrl();
    $_POST[$this->handler->getNonceName()] = wp_create_nonce($this->handler->getPostAction());
    $this->handler->enable();
    do_action('admin_post_' . $this->handler->getPostAction());

    $this->assertTrue($this->handler->didDeny);
    $this->assertEquals('not_enough_permissions', $this->handler->denyReason);
  }

  function test_it_resets_options_if_reset_request() {
    wp_set_current_user(1);

    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_REFERER'] = $this->pluginMeta->getOptionsUrl();
    $_POST[$this->handler->getNonceName()] = wp_create_nonce($this->handler->getPostAction());
    $_POST['reset'] = 'Restore Defaults';

    $this->handler->enable();
    do_action('admin_post_' . $this->handler->getPostAction());

    $this->assertEquals($this->pluginMeta->getOptionsUrl(), $this->handler->redirectTo);
    $this->assertTrue($this->handler->didQuit);
    $this->assertFalse(get_option($this->pluginMeta->getOptionsKey()));
  }

  function test_it_validates_a_request_with_valid_user_input() {
    $rules = array(
        'required' => array(
            array('foo'),
            array('bar')
        ),
        'length' => array(
            array('foo', 3)
        )
    );

    $_POST['foo'] = 'bar';
    $_POST['bar'] = 'two';

    $this->validator->rules = $rules;

    wp_set_current_user(1);
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_REFERER'] = $this->pluginMeta->getOptionsUrl();
    $_POST[$this->handler->getNonceName()] = wp_create_nonce($this->handler->getPostAction());
    $this->handler->enable();
    do_action('admin_post_' . $this->handler->getPostAction());

    $value = $this->flash->getValue();

    $this->assertTrue($value['success']);
    $this->assertEquals($this->pluginMeta->getOptionsUrl(), $this->handler->redirectTo);
    $this->assertTrue($this->handler->didQuit);
  }

  function test_it_validates_a_request_with_invalid_user_input() {
    $rules = array(
        'required' => array(
            array('foo'),
            array('bar')
        ),
        'length' => array(
            array('foo', 3)
        )
    );

    $_POST['foo'] = 'lorem';

    $this->validator->rules = $rules;

    wp_set_current_user(1);
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_REFERER'] = $this->pluginMeta->getOptionsUrl();
    $_POST[$this->handler->getNonceName()] = wp_create_nonce($this->handler->getPostAction());
    $this->handler->enable();
    do_action('admin_post_' . $this->handler->getPostAction());

    $value = $this->flash->getValue();

    $this->assertEquals(2, count($value['errors']));
    $this->assertEquals($this->pluginMeta->getOptionsUrl(), $this->handler->redirectTo);
    $this->assertTrue($this->handler->didQuit);
  }
}
