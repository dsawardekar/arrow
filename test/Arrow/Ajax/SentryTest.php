<?php

namespace Arrow\Ajax;

require_once __DIR__ . '/MockJsonPrinter.php';

use Encase\Container;

class PublicController extends Controller {

  function publicActions() {
    return array('public_one', 'public_two', 'known_missing');
  }

  function actionMethods() {
    return array(
      'public_one' => array('GET'),
      'public_two' => array('POST')
    );
  }

  function public_one() {

  }

  function public_two() {

  }

}

class PrivateController extends Controller {

  function adminActions() {
    return array('admin_one', 'admin_two');
  }

  function admin_one() {

  }

  function admin_two() {

  }


}

class StandardController extends Controller {

  function publicActions() {
    return $this->adminActions();
  }

}

class SentryTest extends \WP_UnitTestCase {

  public $container;
  public $pluginMeta;
  public $sentry;
  public $printer;

  function setUp() {
    parent::setUp();

    $this->pluginMeta = new \Arrow\PluginMeta('ajax-sentry/ajax-sentry.php');
    $this->container  = new Container();
    $this->container
      ->object('pluginMeta', $this->pluginMeta)
      ->singleton('ajaxSentry', 'Arrow\Ajax\Sentry')
      ->singleton('ajaxJsonPrinter', 'Arrow\Ajax\MockJsonPrinter')
      ->singleton('standardController', 'Arrow\Ajax\StandardController')
      ->singleton('publicController', 'Arrow\Ajax\PublicController')
      ->singleton('privateController', 'Arrow\Ajax\PrivateController');

    $this->sentry = $this->container->lookup('ajaxSentry');
    $this->printer = $this->container->lookup('ajaxJsonPrinter');
  }

  function test_it_is_an_arrow_sentry() {
    $this->assertInstanceOf('Arrow\Sentry', $this->sentry);
  }

  function test_it_has_empty_referer_for_public_access() {
    $this->sentry->public = true;
    $this->assertEquals('', $this->sentry->getValidReferer());
  }

  function test_it_has_correct_referer_for_admin_access() {
    $this->sentry->public = false;
    $actual = $this->sentry->getValidReferer();
    $this->assertEquals($this->pluginMeta->getOptionsUrl(), $actual);
  }

  function test_it_allows_public_access_without_valid_referer() {
    $this->sentry->public = true;
    $actual = $this->sentry->isValidReferer();
    $this->assertTrue($actual);
  }

  function test_it_does_not_allow_admin_access_without_valid_referer() {
    $this->sentry->public = false;
    $actual = $this->sentry->isValidReferer();
    $this->assertFalse($actual);
  }

  function test_it_allows_admin_access_with_valid_referer() {
    $this->sentry->public = false;
    $_SERVER['HTTP_REFERER'] = $this->pluginMeta->getOptionsUrl();
    $this->assertTrue($this->sentry->isValidReferer());
  }

  function test_it_allows_non_logged_in_users_for_public_access() {
    $this->sentry->public = true;
    $this->assertFalse($this->sentry->getValidLoggedIn());
  }

  function test_it_only_allows_logged_in_users_for_admin_access() {
    $this->sentry->public = false;
    $this->assertTrue($this->sentry->getValidLoggedIn());
  }

  function test_it_has_blank_controller_if_absent() {
    $actual = $this->sentry->getController();
    $this->assertEquals('', $actual);
  }

  function test_it_has_correct_controller_if_present() {
    $_GET['controller'] = 'foo';
    $this->assertEquals('foo', $this->sentry->getController());
  }

  function test_it_does_not_have_valid_controller_if_absent() {
    $this->assertFalse($this->sentry->isValidController());
  }

  function test_it_does_not_have_valid_controller_if_not_in_container() {
    $_GET['controller'] = 'unknown';
    $this->assertFalse($this->sentry->isValidController());
  }

  function test_it_has_valid_controller_if_present() {
    $_GET['controller'] = 'public';
    $this->assertTrue($this->sentry->isValidController());
  }

  function test_it_has_empty_action_if_absent() {
    $this->assertEquals('', $this->sentry->getAction());
  }

  function test_it_has_correct_action_if_present() {
    $_GET['operation'] = 'foo';
    $this->assertEquals('foo', $this->sentry->getAction());
  }

  function test_it_uses_public_actions_for_public_access() {
    $this->sentry->public = true;
    $controller = $this->container->lookup('publicController');
    $actual = $this->sentry->getAllowedActions($controller);
    $this->assertEquals(array('public_one', 'public_two', 'known_missing'), $actual);
  }

  function test_it_uses_admin_actions_for_admin_access() {
    $this->sentry->public = false;
    $controller = $this->container->lookup('privateController');
    $actual = $this->sentry->getAllowedActions($controller);
    $this->assertEquals(array('admin_one', 'admin_two'), $actual);
  }

  function test_it_does_not_have_valid_action_for_missing_action() {
    $this->assertFalse($this->sentry->isValidAction());
  }

  function test_it_does_not_have_valid_action_for_unknown_controller() {
    $_GET['controller'] = 'foo';
    $_GET['operation']     = 'index';
    $this->assertFalse($this->sentry->isValidAction());
  }

  function test_it_does_not_have_valid_action_for_admin_action_with_public_access() {
    $this->sentry->public = true;
    $_GET['controller']   = 'public';
    $_GET['operation']       = 'private_one';

    $this->assertFalse($this->sentry->isValidAction());
  }

  function test_it_does_not_have_valid_action_for_unknown_action() {
    $this->sentry->public = true;
    $_GET['controller']   = 'public';
    $_GET['operation']       = 'known_missing';

    $this->assertFalse($this->sentry->isValidAction());
  }

  function test_it_has_valid_action_for_public_access() {
    $this->sentry->public = true;
    $_GET['controller'] = 'public';
    $_GET['operation'] = 'public_one';

    $this->assertTrue($this->sentry->isValidAction());
  }

  function test_it_has_valid_action_for_admin_access() {
    $this->sentry->public = false;
    $_GET['controller'] = 'private';
    $_GET['operation'] = 'admin_one';

    $this->assertTrue($this->sentry->isValidAction());
  }

  function test_it_knows_if_index_request_method_is_invalid() {
    $_GET['controller']        = 'public';
    $_GET['operation']            = 'index';
    $_SERVER['REQUEST_METHOD'] = 'POST';

    $this->assertFalse($this->sentry->isValidMethod());
  }

  function test_it_knows_if_index_request_method_is_valid() {
    $_GET['controller']        = 'public';
    $_GET['operation']            = 'index';
    $_SERVER['REQUEST_METHOD'] = 'GET';

    $this->assertTrue($this->sentry->isValidMethod());
  }

  function test_it_knows_if_create_request_is_invalid() {
    $_GET['controller']        = 'public';
    $_GET['operation']            = 'create';
    $_SERVER['REQUEST_METHOD'] = 'DELETE';

    $this->assertFalse($this->sentry->isValidMethod());
  }

  function test_it_knows_if_create_request_is_valid() {
    $_GET['controller']        = 'standard';
    $_GET['operation']            = 'create';
    $_SERVER['REQUEST_METHOD'] = 'POST';

    $this->assertTrue($this->sentry->isValidMethod());
  }

  function test_it_knows_if_custom_request_is_invalid() {
    $_GET['controller']        = 'public';
    $_GET['operation']            = 'public_one';
    $_SERVER['REQUEST_METHOD'] = 'POST';

    $this->assertFalse($this->sentry->isValidMethod());
  }

  function test_it_knows_if_custom_request_is_valid() {
    $_GET['controller']        = 'public';
    $_GET['operation']            = 'public_one';
    $_SERVER['REQUEST_METHOD'] = 'GET';

    $this->assertTrue($this->sentry->isValidMethod());
  }

  function test_it_knows_if_json_params_are_invalid() {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $params = $this->sentry->getParams('{foo}');
    $this->assertFalse($this->sentry->isValidParams());
  }

  function test_it_parses_params_only_once() {
    $params1 = $this->sentry->getParams('{"a":1}');
    $this->sentry->getParams('{"a":2}');

    $this->assertTrue($this->sentry->isValidParams());
    $this->assertEquals(array('a' => 1), $params1);
  }

  function test_it_can_parse_input_json() {
    $params = $this->sentry->getParams('{"foo":"bar"}');
    $this->assertTrue($this->sentry->isValidParams());
    $this->assertEquals(array('foo' => 'bar'), $params);
  }

  function test_it_has_correct_nonce_name_for_admin_access() {
    $this->sentry->public = false;
    $actual = $this->sentry->getNonceName();
    $this->assertEquals('nonce', $actual);
  }

  function test_it_has_empty_nonce_value_for_public_access() {
    $this->sentry->pulic = true;
    $this->assertEquals('', $this->sentry->getNonceValue());
  }

  function test_it_has_correct_nonce_value_for_admin_access() {
    $this->sentry->public = false;
    $_GET['nonce'] = 'foo';
    $this->assertEquals('foo', $this->sentry->getNonceValue());
  }

  function test_it_does_not_need_nonce_for_public_access() {
    $this->sentry->public = true;
    $this->assertTrue($this->sentry->isValidNonce());
  }

  function test_it_needs_valid_nonce_for_admin_access() {
    $this->sentry->public = false;
    $_GET['nonce'] = wp_create_nonce($this->sentry->getNonceName());
    $this->assertTrue($this->sentry->isValidNonce());
  }

  function test_it_knows_if_request_is_not_an_admin_request() {
    $actual = $this->sentry->isAdminRequest();
    $this->assertFalse($actual);
  }

  function test_it_knows_if_request_is_an_admin_request() {
    $_GET['admin'] = '1';
    $actual = $this->sentry->isAdminRequest();
    $this->assertTrue($actual);
  }

  /* integration tests */
  function test_it_will_not_authorize_request_with_invalid_controller() {
    $_GET['controller'] = 'unknown';
    $this->assertFalse($this->sentry->authorizePublic());
    $this->assertEquals('invalid_controller', $this->printer->data);
  }

  function test_it_will_not_authorize_request_with_invalid_action() {
    $_GET['controller'] = 'public';
    $_GET['operation'] = 'unknown';

    $this->assertFalse($this->sentry->authorizePublic());
    $this->assertEquals('invalid_action', $this->printer->data);
  }

  function test_it_will_not_authorize_request_with_invalid_method() {
    $_GET['controller']        = 'public';
    $_GET['operation']            = 'public_one';
    $_SERVER['REQUEST_METHOD'] = 'PATCH';

    $this->assertFalse($this->sentry->authorizePublic());
    $this->assertEquals('invalid_method', $this->printer->data);
  }

  function test_it_will_not_authorize_request_with_invalid_params() {
    $_GET['controller']        = 'public';
    $_GET['operation']            = 'public_two';
    $_SERVER['REQUEST_METHOD'] = 'POST';

    $actual = $this->sentry->authorizePublic();
    $this->assertFalse($actual);
    $this->assertEquals('invalid_params', $this->printer->data);
  }

  function test_it_will_not_authorize_public_request_for_logged_in_user_if_public_is_disabled() {
    wp_set_current_user(1);

    $_GET['controller']        = 'standard';
    $_GET['operation']         = 'index';
    $_GET['admin'] = '0';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET['nonce']             = wp_create_nonce($this->sentry->getNonceName());
    $_SERVER['HTTP_REFERER']   = $this->pluginMeta->getOptionsUrl();

    $actual = $this->sentry->authorize();
    $this->assertFalse($actual);
    $this->assertEquals('invalid_public_admin_access', $this->printer->data);
  }

  function test_it_will_not_authorize_request_without_nonce() {
    $_GET['controller']        = 'standard';
    $_GET['operation']            = 'index';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET['nonce'] = 'foo';
    $_GET['admin'] = '1';

    $this->assertFalse($this->sentry->authorize());
    $this->assertEquals('invalid_nonce', $this->printer->data);
  }

  function test_it_will_not_authorize_request_without_referer() {
    $_GET['controller']        = 'standard';
    $_GET['operation']            = 'index';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET['nonce'] = wp_create_nonce($this->sentry->getNonceName());
    $_GET['admin'] = '1';

    $this->assertFalse($this->sentry->authorize());
    $this->assertEquals('invalid_referer', $this->printer->data);
  }

  function test_it_will_not_authorize_request_for_non_logged_in_users() {
    $_GET['controller']        = 'standard';
    $_GET['operation']            = 'index';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET['nonce'] = wp_create_nonce($this->sentry->getNonceName());
    $_GET['admin'] = '1';
    $_SERVER['HTTP_REFERER'] = $this->pluginMeta->getOptionsUrl();

    $this->assertFalse($this->sentry->authorize());
    $this->assertEquals('not_logged_in', $this->printer->data);
  }

  function test_it_will_not_authorize_request_for_user_without_permissions() {
    $id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
    wp_set_current_user($id);

    $_GET['controller']        = 'standard';
    $_GET['operation']         = 'index';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET['nonce']             = wp_create_nonce($this->sentry->getNonceName());
    $_GET['admin'] = '1';
    $_SERVER['HTTP_REFERER']   = $this->pluginMeta->getOptionsUrl();

    $this->assertFalse($this->sentry->authorize());
    $this->assertEquals('invalid_permissions', $this->printer->data);
  }


  function test_it_can_authorize_valid_request() {
    wp_set_current_user(1);

    $_GET['controller']                       = 'standard';
    $_GET['operation']                           = 'index';
    $_SERVER['REQUEST_METHOD']                = 'GET';
    $_GET['nonce'] = wp_create_nonce($this->sentry->getNonceName());
    $_GET['admin'] = '1';
    $_SERVER['HTTP_REFERER']                  = $this->pluginMeta->getOptionsUrl();

    $this->assertTrue($this->sentry->authorize());
  }
}
