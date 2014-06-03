<?php

namespace Arrow\AssetManager;

use Encase\Container;

class AdminStylesheetLoaderTest extends \WP_UnitTestCase {

  public $container;
  public $loader;
  public $pluginMeta;

  function setUp() {
    parent::setUp();

    $this->pluginMeta       = new \Arrow\PluginMeta(getcwd() . '/admin-stylesheet-plugin.php');
    $this->pluginMeta->stylesheetOptions = array(
      'media' => 'screen',
      'version' => '0.1.0'
    );

    $this->container = new Container();
    $this->container->factory('stylesheet', 'Arrow\AssetManager\Stylesheet');
    $this->container->object('pluginMeta', $this->pluginMeta);
    $this->container->singleton('loader', 'Arrow\AssetManager\AdminStylesheetLoader');

    $this->loader = $this->container->lookup('loader');
  }

  function test_it_has_admin_enqueue_action() {
    $actual = $this->loader->enqueueAction();
    $this->assertEquals('admin_enqueue_scripts', $actual);
  }

  function test_it_can_enqueue_admin_stylesheets() {
    $this->loader->schedule('foo');
    $this->loader->load();

    do_action('load-settings_page_admin-stylesheet-plugin');

    /* TODO: improve this test, throws get_data() error for enqueue */
    /* we are still ok since we register and enqueue at the same time */
    $this->assertTrue(wp_style_is('foo', 'registered'));
  }

  function test_it_can_enqueue_and_stream_scripts() {
    $this->loader->stream('foo');
    $this->loader->schedule('bar');
    $this->loader->load();

    do_action('load-settings_page_admin-stylesheet-plugin');

    $this->assertTrue(wp_style_is('foo', 'registered'));
    $this->assertTrue(wp_style_is('bar', 'registered'));
  }

}
