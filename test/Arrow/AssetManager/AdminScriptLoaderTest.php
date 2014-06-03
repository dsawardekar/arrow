<?php

namespace Arrow\AssetManager;

use Encase\Container;

class AdminScriptLoaderTest extends \WP_UnitTestCase {

  public $container;
  public $loader;
  public $pluginMeta;

  function setUp() {
    parent::setUp();

    $this->pluginMeta       = new \Arrow\PluginMeta(getcwd() . '/admin-script-plugin.php');
    $this->pluginMeta->scriptOptions = array(
      'in_footer' => true,
      'version' => '0.1.0'
    );

    $this->container = new Container();
    $this->container->factory('script', 'Arrow\AssetManager\Script');
    $this->container->object('pluginMeta', $this->pluginMeta);
    $this->container->singleton('loader', 'Arrow\AssetManager\AdminScriptLoader');

    $this->loader = $this->container->lookup('loader');
  }

  function test_it_has_admin_enqueue_action() {
    $actual = $this->loader->enqueueAction();
    $this->assertEquals('admin_enqueue_scripts', $actual);
  }

  function test_it_can_enqueue_admin_scripts() {
    $this->loader->schedule('foo');
    $this->loader->load();

    do_action('load-settings_page_admin-script-plugin');
    do_action('admin_enqueue_scripts');

    $this->assertTrue(wp_script_is('foo', 'enqueued'));
  }

  function test_it_can_enqueue_and_stream_scripts() {
    $this->loader->stream('foo');
    $this->loader->schedule('bar');
    $this->loader->load();

    do_action('load-settings_page_admin-script-plugin');
    do_action('admin_enqueue_scripts');

    $this->assertTrue(wp_script_is('foo', 'enqueued'));
    $this->assertTrue(wp_script_is('bar', 'enqueued'));
  }

}
