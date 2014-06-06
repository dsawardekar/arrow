<?php

namespace Arrow\OptionsManager;

use Encase\Container;

class OptionsSentryTest extends \WP_UnitTestCase {

  public $container;
  public $pluginMeta;
  public $sentry;

  function setUp() {
    parent::setUp();

    $this->pluginMeta = new \Arrow\PluginMeta('my-plugin/my-plugin.php');
    $this->container  = new Container();
    $this->container
      ->object('pluginMeta', $this->pluginMeta)
      ->singleton('optionsSentry', 'Arrow\OptionsManager\OptionsSentry');

    $this->sentry = $this->container->lookup('optionsSentry');
  }

  function test_it_has_plugin_meta() {
    $this->assertSame($this->pluginMeta, $this->sentry->pluginMeta);
  }

  function test_it_has_options_url_referer() {
    $this->assertEquals($this->pluginMeta->getOptionsUrl(), $this->sentry->getValidReferer());
  }

  function test_it_only_allows_logged_in_users() {
    $this->assertTrue($this->sentry->getValidLoggedIn());
  }

  function test_it_needs_manage_options_capability() {
    $this->assertEquals('manage_options', $this->sentry->getValidPermissions());
  }

  function test_it_has_correct_nonce_name() {
    $actual = $this->sentry->getNonceName();
    $this->assertEquals('my_plugin_options_post_wpnonce', $actual);
  }

  function test_it_finds_nonce_value_from_post() {
    $_POST[$this->sentry->getNonceName()] = 'foo';
    $actual = $this->sentry->getNonceValue();

    $this->assertEquals('foo', $actual);
  }

}
