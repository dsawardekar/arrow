<?php

namespace Arrow\Options;

use Encase\Container;

class ManifestTest extends \WP_UnitTestCase {

  public $container;
  public $pluginMeta;
  public $manifest;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container
      ->object('pluginMeta', new \Arrow\PluginMeta('my-plugin.php'))
      ->packager('assetPackager', 'Arrow\Asset\Packager')
      ->singleton('optionsManifest', 'Arrow\Options\Manifest');

    $this->pluginMeta       = $this->lookup('pluginMeta');
    $this->manifest         = $this->lookup('optionsManifest');
    $this->scriptLoader     = $this->lookup('adminScriptLoader');
    $this->stylesheetLoader = $this->lookup('adminStylesheetLoader');
  }

  function lookup($key) {
    return $this->container->lookup($key);
  }

  function test_it_has_plugin_meta() {
    $this->assertSame($this->pluginMeta, $this->manifest->pluginMeta);
  }

  function test_it_has_correct_asset_slugs_in_debug_mode() {
    $this->mockDevAssets();
    $actual = $this->manifest->getAssetSlugs();
    $expected = array(
      'my-plugin/dist/assets/vendor',
      'my-plugin/dist/assets/my-plugin'
    );

    $this->assertEquals($expected, $actual);
  }

  function test_it_has_correct_asset_slugs_in_production_mode() {
    $mockMeta = $this->getMock('Arrow\PluginMeta', array(), array('my-plugin'));
    $mockMeta->expects($this->once())->method('getSlug')->will($this->returnValue('my-plugin'));
    $mockMeta->expects($this->once())->method('getDebug')->will($this->returnValue(false));

    $this->manifest->pluginMeta = $mockMeta;
    $actual = $this->manifest->getAssetSlugs();
    $expected = array(
      'my-plugin-vendor',
      'my-plugin-app'
    );

    $this->assertEquals($expected, $actual);
  }

  function test_it_uses_plugin_meta_script_options() {
    $mockMeta = $this->getMock('Arrow\PluginMeta', array(), array('my-plugin'));
    $mockMeta->expects($this->once())->method('getScriptOptions')->will($this->returnValue('options'));

    $this->manifest->pluginMeta = $mockMeta;
    $actual = $this->manifest->getScriptOptions();

    $this->assertEquals('options', $actual);
  }

  function test_it_uses_plugin_meta_stylesheet_options() {
    $mockMeta = $this->getMock('Arrow\PluginMeta', array(), array('my-plugin'));
    $mockMeta->expects($this->once())->method('getStylesheetOptions')->will($this->returnValue('options'));

    $this->manifest->pluginMeta = $mockMeta;
    $actual = $this->manifest->getStylesheetOptions();

    $this->assertEquals('options', $actual);
  }

  function test_it_does_not_have_static_templates() {
    $this->assertEmpty($this->manifest->getTemplates());
  }

  function test_it_knows_if_it_does_not_have_dev_assets() {
    $this->assertFalse($this->manifest->hasDevAssets());
  }

  function mockDevAssets($value = true) {
    $this->manifest = \Mockery::mock('Arrow\Options\Manifest[hasDevAssets,getValidSlugs]');
    $this->manifest->shouldReceive('hasDevAssets')->andReturn($value);
    $this->manifest->shouldReceive('getValidSlugs')->andReturnUsing(array($this, 'returnSlugs'));
    $this->container->inject($this->manifest);
  }

  function returnSlugs($slugs, $type) {
    return $slugs;
  }

  function test_it_has_correct_scripts_in_debug_mode() {
    $this->mockDevAssets();

    $actual = $this->manifest->getScripts();
    $expected = array(
      'my-plugin/dist/assets/vendor',
      'my-plugin/dist/assets/my-plugin',
      'my-plugin-app-run'
    );

    $this->assertEquals($expected, $actual);
  }

  function test_it_has_correct_scripts_in_production_mode() {
    $this->mockDevAssets();
    $mockMeta = $this->getMock('Arrow\PluginMeta', array(), array('my-plugin'));
    $mockMeta->expects($this->any())->method('getSlug')->will($this->returnValue('my-plugin'));
    $mockMeta->expects($this->once())->method('getDebug')->will($this->returnValue(false));
    $this->manifest->pluginMeta = $mockMeta;

    $actual = $this->manifest->getScripts();
    $expected = array(
      'my-plugin-vendor',
      'my-plugin-app',
      'my-plugin-app-run'
    );

    $this->assertEquals($expected, $actual);
  }

  function test_it_has_correct_styles_in_debug_mode() {
    $this->mockDevAssets();
    $actual = $this->manifest->getStyles();
    $expected = array(
      'my-plugin/dist/assets/vendor',
      'my-plugin/dist/assets/my-plugin'
    );

    $this->assertEquals($expected, $actual);
  }

  function test_it_has_correct_styles_in_production_mode() {
    $this->mockDevAssets();

    $mockMeta = $this->getMock('Arrow\PluginMeta', array(), array('my-plugin'));
    $mockMeta->expects($this->any())->method('getSlug')->will($this->returnValue('my-plugin'));
    $mockMeta->expects($this->once())->method('getDebug')->will($this->returnValue(false));
    $this->manifest->pluginMeta = $mockMeta;

    $actual = $this->manifest->getStyles();
    $expected = array(
      'my-plugin-vendor',
      'my-plugin-app',
    );

    $this->assertEquals($expected, $actual);
  }
}
