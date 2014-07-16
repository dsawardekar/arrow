<?php

namespace Arrow\Asset\Manifest;

use Encase\Container;

class ManifestTest extends \WP_UnitTestCase {

  public $container;
  public $manifest;
  public $scriptLoader;
  public $stylesheetLoader;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container
      ->object('pluginMeta', new \Arrow\PluginMeta('my-plugin.php'))
      ->packager('assetPackager', 'Arrow\Asset\Packager')
      ->factory('manifest', 'Arrow\Asset\Manifest\Manifest');

    $this->manifest         = $this->container->lookup('manifest');
    $this->scriptLoader     = $this->container->lookup('adminScriptLoader');
    $this->stylesheetLoader = $this->container->lookup('adminStylesheetLoader');

  }

  function test_it_has_a_container() {
    $this->assertSame($this->container, $this->manifest->container);
  }

  function getContext($asset) {
    return array();
  }

  function test_it_stores_context() {
    $this->manifest->setContext(array($this, 'getContext'));
    $this->assertTrue($this->manifest->hasContext());
  }

  function test_it_can_lookup_items_inside_container() {
    $this->container->object('foo', 'bar');
    $this->assertEquals('bar', $this->manifest->lookup('foo'));
  }

  function test_it_store_loader_mode() {
    $this->manifest->setLoaderMode('stream');
    $mode = $this->manifest->getLoaderMode();
    $this->assertEquals('stream', $mode);
  }

  function test_it_schedules_assets_by_default() {
    $this->assertEquals('schedule', $this->manifest->getLoaderMode());
  }

  function test_it_uses_admin_script_loader_for_admin() {
    $this->manifest->admin = true;
    $actual = $this->manifest->getScriptLoader();
    $this->assertInstanceOf('Arrow\Asset\AdminScriptLoader', $actual);
  }

  function test_it_uses_script_loader_for_non_admin() {
    $this->manifest->admin = false;
    $actual = $this->manifest->getScriptLoader();
    $this->assertInstanceOf('Arrow\Asset\ScriptLoader', $actual);
    $this->assertNotInstanceOf('Arrow\Asset\AdminScriptLoader', $actual);
  }

  function test_it_uses_admin_stylesheet_loader_for_admin() {
    $this->manifest->admin = true;
    $actual = $this->manifest->getStylesheetLoader();
    $this->assertInstanceOf('Arrow\Asset\AdminStylesheetLoader', $actual);
  }

  function test_it_uses_stylesheet_loader_for_non_admin() {
    $this->manifest->admin = false;
    $actual = $this->manifest->getStylesheetLoader();
    $this->assertInstanceOf('Arrow\Asset\StylesheetLoader', $actual);
    $this->assertNotInstanceOf('Arrow\Asset\AdminStylesheetLoader', $actual);
  }

  function test_it_has_asset_accessor_api() {
    $this->assertTrue(method_exists($this->manifest, 'getScripts'));
    $this->assertTrue(method_exists($this->manifest, 'getScriptOptions'));
    $this->assertTrue(method_exists($this->manifest, 'getStyles'));
    $this->assertTrue(method_exists($this->manifest, 'getStylesheetOptions'));
    $this->assertTrue(method_exists($this->manifest, 'getTemplates'));
    $this->assertTrue(method_exists($this->manifest, 'includeTemplate'));
  }

  function test_it_wont_load_scripts_if_empty() {
    $this->manifest->scripts = array();
    $this->manifest->loadScripts();

    $this->assertFalse($this->scriptLoader->loaded());
  }

  function test_it_will_load_scripts_if_valid() {
    $this->manifest->scripts = array(
      'foo', 'bar'
    );

    $this->manifest->loadScripts();

    $this->assertTrue($this->scriptLoader->isScheduled('foo'));
    $this->assertTrue($this->scriptLoader->isScheduled('bar'));
  }

  function test_it_will_stream_scripts_if_valid() {
    $this->manifest->scripts = array(
      'foo', 'bar'
    );

    $this->manifest->setLoaderMode('stream');
    $this->manifest->loadScripts();

    $this->assertTrue($this->scriptLoader->isStreamed('foo'));
    $this->assertTrue($this->scriptLoader->isStreamed('bar'));
  }

  function test_it_will_localize_the_last_script_if_context_is_valid() {
    $this->manifest->setContext(array($this, 'getContext'));
    $this->manifest->scripts = array('foo', 'bar');
    $this->manifest->loadScripts();

    $script = $this->scriptLoader->find('bar');
    $this->assertTrue(is_callable($script->localizer));
  }

  function test_it_will_localize_the_only_script_if_context_is_valid() {
    $this->manifest->setContext(array($this, 'getContext'));
    $this->manifest->scripts = array('foo');
    $this->manifest->loadScripts();

    $script = $this->scriptLoader->find('foo');
    $this->assertTrue(is_callable($script->localizer));
  }

  function test_it_can_load_options_scripts_in_correct_order() {
    $this->manifest->scripts = array(
      'jquery',
      'handlebars',
      'ember',
      'ember-validations',
      'ember-easyForm',
      'my-plugin-app'
    );

    $this->manifest->loadScripts();

    $this->assertEquals(
      array('jquery'),
      $this->scriptLoader->find('handlebars')->dependencies
    );
    $this->assertEquals(
      array('handlebars'),
      $this->scriptLoader->find('ember')->dependencies
    );
    $this->assertEquals(
      array('ember'),
      $this->scriptLoader->find('ember-validations')->dependencies
    );
    $this->assertEquals(
      array('ember-validations'),
      $this->scriptLoader->find('ember-easyForm')->dependencies
    );
    $this->assertEquals(
      array('ember-easyForm'),
      $this->scriptLoader->find('my-plugin-app')->dependencies
    );
  }

  function test_it_will_not_load_styles_if_empty() {
    $this->manifest->styles = array();
    $this->manifest->loadStyles();

    $this->assertFalse($this->stylesheetLoader->loaded());
  }

  function test_it_will_load_styles_if_valid() {
    $this->manifest->styles = array('foo', 'bar');
    $this->manifest->loadStyles();

    $this->assertTrue($this->stylesheetLoader->isScheduled('foo'));
    $this->assertTrue($this->stylesheetLoader->isScheduled('bar'));
  }

  function test_it_can_load_templates() {
    $this->manifest->templates = array(
      'test/templates/manifest/foo.php',
      'test/templates/manifest/bar.php'
    );

    $GLOBALS['MANIFEST_TEMPLATES'] = array();
    $this->manifest->loadTemplates();

    $this->assertEquals('foo', $GLOBALS['MANIFEST_TEMPLATES'][0]);
    $this->assertEquals('bar', $GLOBALS['MANIFEST_TEMPLATES'][1]);
  }

  function test_it_can_load_manifest() {
    $this->manifest->scripts   = array('a', 'b');
    $this->manifest->styles    = array('c', 'd');
    $this->manifest->templates = array(
      'test/templates/manifest/foo.php',
      'test/templates/manifest/bar.php'
    );

    $GLOBALS['MANIFEST_TEMPLATES'] = array();
    $this->manifest->load();
    $this->manifest->loadTemplates();

    $this->assertTrue($this->scriptLoader->isScheduled('a'));
    $this->assertTrue($this->scriptLoader->isScheduled('b'));

    $this->assertTrue($this->stylesheetLoader->isScheduled('c'));
    $this->assertTrue($this->stylesheetLoader->isScheduled('d'));

    $this->assertEquals('foo', $GLOBALS['MANIFEST_TEMPLATES'][0]);
    $this->assertEquals('bar', $GLOBALS['MANIFEST_TEMPLATES'][1]);
  }

  function test_it_will_not_load_manifest_if_already_loaded() {
    $this->assertFalse($this->manifest->loaded());

    $this->manifest->load();
    $this->manifest->didLoad = 'already_loaded';
    $this->manifest->load();

    $this->assertEquals('already_loaded', $this->manifest->didLoad);
  }

}
