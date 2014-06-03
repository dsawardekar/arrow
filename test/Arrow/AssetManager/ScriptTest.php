<?php

namespace Arrow\AssetManager;

use Encase\Container;

class ScriptTest extends \WP_UnitTestCase {

  public $container;
  public $pluginMeta;
  public $script;

  function setUp() {
    parent::setUp();

    $this->pluginMeta       = new \Arrow\PluginMeta(getcwd() . '/my-plugin.php');
    $this->pluginMeta->scriptOptions = array(
      'in_footer' => true,
      'version' => '0.1.0'
    );

    $this->container = new Container();
    $this->container->singleton('script', 'Arrow\AssetManager\Script');
    $this->container->object('pluginMeta', $this->pluginMeta);

    $this->script = $this->container->lookup('script');
  }

  function test_it_has_js_dirname() {
    $this->assertEquals('js', $this->script->dirname());
  }

  function test_it_has_js_extension() {
    $this->assertEquals('.js', $this->script->extension());
  }

  function test_it_has_default_options() {
    $actual = $this->script->defaultOptions();
    $this->assertEquals($this->pluginMeta->scriptOptions, $actual);
  }

  function test_it_can_register_script() {
    $this->script->slug = 'foo';
    $this->script->register();
    $this->assertTrue(wp_script_is('foo', 'registered'));
  }

  function test_it_can_pickup_default_option() {
    $actual = $this->script->option('version');
    $this->assertEquals('0.1.0', $actual);
  }

  function test_it_runs_localizer_if_present() {
    $this->script->slug = 'foo';
    $this->script->localizer = array($this, 'onLocalize');
    $this->script->register();
    $this->assertTrue($this->script->localized);
  }

  function onLocalize($script) {
    return array();
  }

  function test_it_can_enqueue_script() {
    $this->script->slug = 'foo';
    $this->script->register();
    $this->script->enqueue();
    $this->assertTrue(wp_script_is('foo', 'enqueued'));
  }

}
