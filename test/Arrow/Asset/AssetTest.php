<?php

namespace Arrow\Asset;

use Encase\Container;

class AssetTest extends \WP_UnitTestCase {

  public $container;
  public $asset;
  public $pluginMeta;

  function setUp() {
    parent::setUp();

    $this->pluginMeta       = new \Arrow\PluginMeta(getcwd() . '/my-plugin.php');

    $this->container = new Container();
    $this->container->singleton('asset', 'Arrow\Asset\Asset');
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

  function test_it_can_detect_app_slug() {
    $this->asset->slug = 'my-plugin/dist/assets/foo';
    $this->assertTrue($this->asset->isAppSlug());
  }

  function test_it_can_detect_non_app_slug() {
    $this->asset->slug = 'appfoo';
    $this->assertFalse($this->asset->isAppSlug());
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

    $this->assertEquals("my-plugin-foo", $actual);
  }

  function test_it_can_build_unique_custom_slug() {
    $this->asset->slug = 'theme-custom';
    $actual = $this->asset->uniqueSlug();

    $this->assertEquals("my-plugin-custom", $actual);
  }

  function test_it_can_build_unique_app_slug() {
    $this->asset->slug = 'my-plugin/dist/assets/foo';
    $actual = $this->asset->uniqueSlug();

    $this->assertEquals('my-plugin/dist/assets/foo', $actual);
  }

  function test_it_can_build_asset_filepath() {
    $this->asset->slug = 'foo';
    $actual = $this->asset->filepath();

    $this->assertStringEndsWith('assets/foo.js', $actual);
  }

  function test_it_can_build_minified_asset_filepath() {
    $this->asset->slug = 'foo';
    $actual = $this->asset->filepath(true);

    $this->assertStringEndsWith('assets/foo.min.js', $actual);
  }

  function test_it_knows_if_asset_filepath_does_not_exist() {
    $this->asset->slug = 'foo';
    $this->assertFalse($this->asset->exists());
  }

  function test_it_knows_if_asset_filepath_exists() {
    $this->asset->slug = 'sample';
    $this->assertTrue($this->asset->exists());
  }

  function test_it_knows_if_minified_asset_filepath_does_not_exist() {
    $this->asset->slug = 'foo';
    $this->assertFalse($this->asset->exists(true));
  }

  function test_it_knows_if_minified_asset_filepath_exists() {
    $this->asset->slug = 'sample';
    $this->assertTrue($this->asset->exists(true));
  }

  function test_it_will_not_minify_if_disabled() {
    $this->asset->slug = 'foo';
    $this->assertFalse($this->asset->canMinify());
  }

  function test_it_will_not_minify_if_minified_asset_is_not_present() {
    $this->asset->slug = 'one';
    $this->pluginMeta->minify = true;
    $this->assertFalse($this->asset->canMinify());
  }

  function test_it_will_minify_if_minified_asset_is_present() {
    $this->asset->slug = 'sample';
    $this->pluginMeta->minify = true;
    $this->assertTrue($this->asset->canMinify());
  }

  function test_it_will_not_use_minified_path_if_disabled() {
    $this->asset->slug = 'sample';
    $this->pluginMeta->minify = false;
    $this->assertEquals('assets/sample.js', $this->asset->relpath());
  }

  function test_it_will_use_minified_path_if_can_minify() {
    $this->asset->slug = 'sample';
    $this->pluginMeta->minify = true;
    $this->assertEquals('assets/sample.min.js', $this->asset->relpath());
  }
}
