<?php

namespace Arrow\AssetManager;

use Encase\Container;

class ScriptLoaderTest extends \WP_UnitTestCase {

  public $container;
  public $loader;
  public $pluginMeta;

  function setUp() {
    parent::setUp();

    $this->pluginMeta       = new PluginMeta();
    $this->pluginMeta->file = getcwd() . '/script-plugin.php';
    $this->pluginMeta->dir  = getcwd();
    $this->pluginMeta->slug = 'script_plugin';
    $this->pluginMeta->scriptOptions = array(
      'in_footer' => true,
      'version' => '0.1.0'
    );

    $this->container = new Container();
    $this->container->factory('script', 'Arrow\AssetManager\Script');
    $this->container->object('pluginMeta', $this->pluginMeta);
    $this->container->singleton('loader', 'Arrow\AssetManager\ScriptLoader');

    $this->loader = $this->container->lookup('loader');
  }

  function test_it_has_script_asset_type() {
    $this->assertEquals('script', $this->loader->assetType());
  }

  function test_it_can_enqueue_scripts() {
    $this->loader->schedule('foo');
    $this->loader->register();
    $this->loader->enqueue();

    do_action('wp_enqueue_scripts');

    $this->assertTrue(wp_script_is('foo', 'enqueued'));
  }

  function test_it_can_stream_scripts() {
    $this->loader->stream('foo');

    $this->assertTrue(wp_script_is('foo', 'enqueued'));
  }

  function test_it_can_enqueue_and_stream_scripts_at_the_same_time() {
    $this->loader->stream('bar');
    $this->loader->schedule('foo');
    $this->loader->load();

    do_action('wp_enqueue_scripts');

    $this->assertTrue(wp_script_is('foo', 'enqueued'));
    $this->assertTrue(wp_script_is('bar', 'enqueued'));
  }

}
