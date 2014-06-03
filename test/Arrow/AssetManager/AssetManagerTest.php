<?php

namespace Arrow\AssetManager;

use Encase\Container;

class AssetManagerTest extends \WP_UnitTestCase {

  public $container;
  public $assetManager;
  public $pluginMeta;

  function setUp() {
    parent::setUp();

    $this->pluginMeta       = new \Arrow\PluginMeta(getcwd() . '/my-plugin.php');
    $this->pluginMeta->scriptOptions = array(
      'in_footer' => true,
      'version' => '0.1.0'
    );
    $this->pluginMeta->stylesheetOptions = array(
      'media' => 'screen',
      'version' => '0.1.0'
    );

    $this->container = new Container();
    $this->container->object('pluginMeta', $this->pluginMeta);
    $this->assetManager = new AssetManager($this->container);
  }

  function lookup($key) {
    return $this->container->lookup($key);
  }

  function test_it_adds_a_script_to_the_container() {
    $script = $this->lookup('script');
    $this->assertInstanceOf('Arrow\AssetManager\Script', $script);
  }

  function test_it_adds_a_script_factory_to_the_container() {
    $script1 = $this->lookup('script');
    $script2 = $this->lookup('script');

    $this->assertNotSame($script1, $script2);
  }

  function test_it_adds_a_stylesheet_to_the_container() {
    $stylesheet = $this->lookup('stylesheet');
    $this->assertInstanceOf('Arrow\AssetManager\Stylesheet', $stylesheet);
  }

  function test_it_adds_a_stylesheet_factory_to_the_container() {
    $stylesheet1 = $this->lookup('stylesheet');
    $stylesheet2 = $this->lookup('stylesheet');

    $this->assertNotSame($stylesheet1, $stylesheet2);
  }

  function test_it_adds_a_script_loader_to_the_container() {
    $loader = $this->lookup('scriptLoader');
    $this->assertInstanceOf('Arrow\AssetManager\ScriptLoader', $loader);
  }

  function test_it_adds_a_singleton_script_loader_to_the_container() {
    $loader1 = $this->lookup('scriptLoader');
    $loader2 = $this->lookup('scriptLoader');

    $this->assertSame($loader1, $loader2);
  }

  function test_it_adds_a_stylesheet_loader_to_the_container() {
    $loader = $this->lookup('stylesheetLoader');
    $this->assertInstanceOf('Arrow\AssetManager\StylesheetLoader', $loader);
  }

  function test_it_adds_a_singleton_stylesheet_loader_to_the_container() {
    $loader1 = $this->lookup('stylesheetLoader');
    $loader2 = $this->lookup('stylesheetLoader');

    $this->assertSame($loader1, $loader2);
  }

  function test_it_adds_an_admin_script_loader_to_the_container() {
    $loader = $this->lookup('adminScriptLoader');
    $this->assertInstanceOf('Arrow\AssetManager\AdminScriptLoader', $loader);
  }

  function test_it_adds_a_singleton_admin_script_loader_to_the_container() {
    $loader1 = $this->lookup('adminScriptLoader');
    $loader2 = $this->lookup('adminScriptLoader');

    $this->assertSame($loader1, $loader2);
  }

  function test_it_adds_an_admin_stylesheet_loader_to_the_container() {
    $loader = $this->lookup('adminStylesheetLoader');
    $this->assertInstanceOf('Arrow\AssetManager\AdminStylesheetLoader', $loader);
  }

  function test_it_adds_a_singleton_admin_stylesheet_loader_to_the_container() {
    $loader1 = $this->lookup('adminStylesheetLoader');
    $loader2 = $this->lookup('adminStylesheetLoader');

    $this->assertSame($loader1, $loader2);
  }

}
