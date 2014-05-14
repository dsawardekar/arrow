<?php

namespace Arrow\AssetManager;

use Encase\Container;

class AssetTest extends \WP_UnitTestCase {

  public $container;
  public $asset;
  public $pluginMeta;

  function setUp() {
    parent::setUp();

    $this->pluginMeta       = new PluginMeta();
    $this->pluginMeta->file = getcwd() . '/my-plugin.php';
    $this->pluginMeta->dir  = getcwd();
    $this->pluginMeta->slug = 'my_plugin';

    $this->container = new Container();
    $this->container->singleton('asset', 'Arrow\AssetManager\Asset');
    $this->container->object('pluginMeta', $this->pluginMeta);

    $this->asset = $this->container->lookup('asset');
  }

  function test_it_has_a_container() {
    $this->assertInstanceOf('Encase\\Container', $this->asset->container);
  }

  function test_it_has_plugin_meta() {
    $this->assertEquals($this->pluginMeta, $this->asset->pluginMeta);
  }

  function test_it_has_a_default_directory() {
    $this->assertEquals('assets', $this->asset->dirname());
  }

  function test_it_has_a_default_extension() {
    $this->assertEquals('.js', $this->asset->extension());
  }

  function test_it_can_build_relative_path() {
    $this->asset->slug = 'foo';
    $path = $this->asset->relpath();

    $this->assertEquals('assets/foo.js', $path);
  }

  function test_it_can_build_path_to_plugin_asset() {
    $this->asset->slug = 'foo';
    $parent = dirname($this->pluginMeta->getFile());
    $expected = $parent . '/assets/foo.js';
    $actual = $this->asset->path();

    $this->assertStringEndsWith($expected, $actual);
  }

  function test_it_can_detect_a_custom_slug() {
    $this->asset->slug = 'theme-custom';
    $this->assertTrue($this->asset->isCustomSlug());
  }

  function test_it_can_detect_normal_slug() {
    $this->asset->slug = 'wp-scroll-up-foo';
    $this->assertFalse($this->asset->isCustomSlug());
  }

  function test_it_can_build_custom_path() {
    $this->asset->slug = 'theme-foo';
    $expected = 'my-plugin/foo.js';
    $this->assertStringEndsWith($expected, $this->asset->path());
  }

  function test_it_can_build_subdir_path() {
    $this->asset->slug = 'languages/foo-bar';
    $expected = 'assets/languages/foo-bar.js';
    $this->assertStringEndsWith($expected, $this->asset->path());
  }

  function test_it_can_store_options() {
    $this->asset->options = array('in_footer' => true);
    $this->assertTrue($this->asset->option('in_footer'));
  }

  function test_it_defaults_to_false_for_unknown_option() {
    $this->asset->options = array();
    $this->assertFalse($this->asset->option('in_footer'));
  }

  function test_it_can_build_localize_slug_name() {
    $this->asset->slug = 'my-plugin';
    $actual = $this->asset->localizeSlug();
    $this->assertEquals('my_plugin', $actual);
  }

  function test_it_can_run_localizer() {
    $this->asset->slug = 'foo';
    $this->asset->localizer = array($this, 'onLocalize');
    $result = $this->asset->runLocalizer();
    $this->assertEquals(array('foo' => 'bar'), $result);
  }

  function onLocalize($asset) {
    return array('foo' => 'bar');
  }

  function test_it_can_build_unique_slug() {
    $this->asset->slug = 'theme-foo';
    $actual = $this->asset->uniqueSlug();

    $this->assertEquals("my_plugin-foo", $actual);
  }

  function test_it_can_build_unique_custom_slug() {
    $this->asset->slug = 'theme-custom';
    $actual = $this->asset->uniqueSlug();

    $this->assertEquals("my_plugin-custom", $actual);
  }
}
