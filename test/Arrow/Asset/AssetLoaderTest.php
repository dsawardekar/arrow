<?php

namespace Arrow\Asset;

use Encase\Container;

class AssetLoaderTest extends \WP_UnitTestCase {

  public $container;
  public $loader;
  public $pluginMeta;

  function setUp() {
    parent::setUp();

    $this->pluginMeta       = new \Arrow\PluginMeta(getcwd() . '/foo-plugin.php');
    $this->pluginMeta->scriptOptions = array(
      'in_footer' => true,
      'version' => '0.1.0'
    );

    $this->container = new Container();
    $this->container->object('pluginMeta', $this->pluginMeta);
    $this->container->factory('asset', 'Arrow\Asset\Script');
    $this->container->singleton('loader', 'Arrow\Asset\AssetLoader');

    $this->loader = $this->container->lookup('loader');
  }

  function test_it_has_a_container() {
    $this->assertInstanceOf('Encase\\Container', $this->loader->container);
  }

  function test_it_is_not_loaded_initially() {
    $this->assertFalse($this->loader->loaded());
  }

  function test_it_can_schedule_asset_for_loading() {
    $this->loader->schedule('foo');
    $script = $this->loader->find('foo');
    $this->assertEquals('foo', $script->slug);
  }

  function test_it_knows_if_asset_is_scheduled() {
    $this->loader->schedule('foo');
    $this->assertTrue($this->loader->isScheduled('foo'));
  }

  function test_it_knows_if_asset_is_not_scheduled() {
    $this->assertFalse($this->loader->isScheduled('foo'));
  }

  function test_it_can_schedule_asset_with_options() {
    $this->loader->schedule('foo', array('lorem' => true));
    $script = $this->loader->find('foo');
    $this->assertTrue($script->options['lorem']);
  }

  function test_it_can_add_dependencies_to_assets() {
    $this->loader->schedule('foo');
    $this->loader->dependency('foo', array('jquery', 'jquery-ui'));
    $script = $this->loader->find('foo');

    $this->assertEquals(array('jquery', 'jquery-ui'), $script->dependencies);
  }

  function test_it_can_add_dependencies_to_assets_in_options() {
    $dependencies = array('jquery', 'jquery-ui');
    $this->loader->schedule('foo', array('dependencies' => $dependencies));
    $script = $this->loader->find('foo');

    $this->assertEquals(array('jquery', 'jquery-ui'), $script->dependencies);
  }

  function test_it_can_localize_assets() {
    $localizer = array($this, 'localize');
    $this->loader->schedule('foo');
    $this->loader->localize('foo', $localizer);
    $script = $this->loader->find('foo');

    $this->assertEquals($localizer, $script->localizer);
  }

  function localize($script) {
    return array();
  }

  function test_it_can_register_scripts() {
    $this->loader->schedule('foo');
    $this->loader->register();

    $this->assertTrue(wp_script_is('foo', 'registered'));
  }

  function test_it_can_enqueue_scripts() {
    $this->loader->schedule('foo');
    $this->loader->register();
    $this->loader->doEnqueue();

    $this->assertTrue(wp_script_is('foo', 'enqueued'));
  }

  function test_it_can_stream_scripts() {
    $this->loader->stream('foo');
    $this->assertTrue(wp_script_is('foo', 'enqueued'));
  }

  function test_it_can_find_scheduled_scripts() {
    $this->loader->schedule('foo', array('foo' => 1));
    $asset = $this->loader->find('foo');
    $this->assertEquals(1, $asset->options['foo']);
  }

  function test_it_can_find_streamed_scripts() {
    $this->loader->stream('foo', array('foo' => 1));
    $asset = $this->loader->find('foo');
    $this->assertEquals(1, $asset->options['foo']);
  }

  function test_it_wont_find_unknown_scripts() {
    $actual = $this->loader->find('foo');
    $this->assertFalse($actual);
  }

  function test_it_knows_if_stream_was_previously_streamed() {
    $this->loader->stream('foo');
    $this->assertTrue(wp_script_is('foo', 'enqueued'));
    $this->assertTrue($this->loader->isStreamed('foo'));
  }

  function test_it_knows_if_script_is_not_streamed() {
    $this->assertFalse($this->loader->isStreamed('foo'));
  }

  function test_it_wont_stream_same_slug_twice() {
    $this->loader->stream('foo');
    $this->loader->stream('foo');
    $this->assertTrue(wp_script_is('foo', 'enqueued'));
    $this->assertTrue($this->loader->isStreamed('foo'));
  }

  function test_it_can_schedule_and_stream_at_the_time() {
    $this->loader->stream('foo');
    $this->loader->schedule('bar');
    $this->loader->load();

    do_action('wp_enqueue_scripts');

    $this->assertTrue(wp_script_is('foo', 'enqueued'));
    $this->assertTrue(wp_script_is('bar', 'enqueued'));
  }

  function test_it_will_only_load_once_with_schedule() {
    $this->loader->schedule('foo', array('foo' => 1));
    $this->loader->load();
    $this->loader->load();

    $asset = $this->loader->find('foo');
    $this->assertEquals(1, $asset->options['foo']);
  }

}
