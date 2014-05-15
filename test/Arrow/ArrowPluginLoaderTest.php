<?php

require_once __DIR__ . '/../../lib/Arrow/ArrowPluginLoader.php';

class ArrowPluginLoaderTest extends \WP_UnitTestCase {

  public $loader;
  public $didLoad     = false;
  public $didReady    = false;
  public $didCallback = false;
  public $pluginNames = array();
  public $file;
  public $one;
  public $two;
  public $three;

  function setUp() {
    parent::setUp();

    $this->loader = new ArrowPluginLoader();
    $this->file = getcwd() . '/my-plugin.php';

    $GLOBALS['arrowPlugins'] = array();

    $plugins     = getcwd() . '/test/plugins';
    $this->one   = $plugins . '/one/one.php';
    $this->two   = $plugins . '/two/two.php';
    $this->three = $plugins . '/three/three.php';
  }

  function pluginLoaded() {
    $this->didLoad = true;
  }

  function pluginReady() {
    $this->didReady = true;
  }

  function pluginCallback() {
    $this->didCallback = true;
    array_push($this->pluginNames, $this->loader->currentPlugin);
  }

  function test_it_knows_if_plugin_is_not_registered() {
    $this->assertFalse($this->loader->isRegistered($this->file));
  }

  function test_it_knows_if_plugin_is_registered() {
    $this->loader->register(
      $this->file, '0.1.0', array($this, 'onPluginLoad')
    );

    $this->assertTrue($this->loader->isRegistered($this->file));
  }

  function test_it_knows_if_plugin_a_is_greater_than_b() {
    $a = array(
      'file' => 'a',
      'arrowVersion' => '0.2.0'
    );

    $b = array(
      'file' => 'b',
      'arrowVersion' => '0.1.0'
    );

    $actual = $this->loader->comparePlugins($a, $b);
    $this->assertEquals(1, $actual);
  }

  function test_it_knows_if_plugin_a_is_less_than_b() {
    $a = array(
      'file' => 'a',
      'arrowVersion' => '0.1.0'
    );

    $b = array(
      'file' => 'b',
      'arrowVersion' => '0.2.0'
    );

    $actual = $this->loader->comparePlugins($a, $b);
    $this->assertEquals(-1, $actual);
  }

  function test_it_knows_if_plugin_a_equals_b() {
    $a = array(
      'name' => 'a',
      'arrowVersion' => '0.1.0'
    );

    $b = array(
      'name' => 'b',
      'arrowVersion' => '0.1.0'
    );

    $actual = $this->loader->comparePlugins($a, $b);
    $this->assertEquals(0, $actual);
  }

  function getKeys(&$items) {
    $keys = array();
    foreach ($items as $item) {
      array_push($keys, $item['file']);
    }

    return $keys;
  }

  function test_it_can_sort_plugins_into_correct_order() {
    $this->loader->register('a', '3.0', null);
    $this->loader->register('b', '2.0', null);
    $this->loader->register('c', '1.0', null);

    $plugins = $this->loader->sortPlugins();
    $keys = $this->getKeys($plugins);

    $this->assertEquals(array('c', 'b', 'a'), $keys);
  }

  function test_it_can_sort_semver_plugins_into_correct_order() {
    $this->loader->register('a', '1.0.5', null);
    $this->loader->register('b', '1.7.1', null);
    $this->loader->register('c', '1.6.2', null);

    $plugins = $this->loader->sortPlugins();
    $keys = $this->getKeys($plugins);

    $this->assertEquals(array('a', 'c', 'b'), $keys);
  }

  function test_it_can_build_path_to_plugins_autoloader() {
    $one = getcwd() . '/test/plugins/one/one.php';
    $actual = $this->loader->getPluginAutoloadPath($one);
    $this->assertEquals(getcwd() . '/test/plugins/one/vendor/autoload.php', $actual);
  }

  function test_it_can_load_plugins_autoloader() {
    $path = getcwd() . '/test/plugins/one/vendor/autoload.php';
    $this->loader->requirePluginAutoload($path);

    $this->assertEquals(array('one'), $GLOBALS['arrowPlugins']);
  }

  function test_it_does_not_require_autoload_if_missing() {
    $path = getcwd() . '/test/plugins/missing/vendor/autoload.php';
    $this->loader->requirePluginAutoload($path);
    $this->assertEquals(array(), $GLOBALS['arrowPlugins']);
  }

  function test_it_can_require_plugins_autoloader_from_file() {
    $plugin = array(
      'file' => getcwd() . '/test/plugins/one/one.php'
    );

    $this->loader->requirePlugin($plugin);

    $this->assertEquals(array('one'), $GLOBALS['arrowPlugins']);
  }

  function test_it_can_send_plugin_event() {
    add_action('arrow-plugin-my-plugin-loaded', array($this, 'pluginLoaded'));
    $this->loader->sendPluginEvent('my-plugin', 'loaded');
    $this->assertTrue($this->didLoad);
  }

  function test_it_sends_plugin_events_on_plugin_load() {
    $plugin = array(
      'file' => 'my-plugin',
      'callback' => null
    );

    add_action('arrow-plugin-my-plugin-loaded', array($this, 'pluginLoaded'));
    add_action('arrow-plugin-my-plugin-ready', array($this, 'pluginReady'));

    $this->loader->loadPlugin($plugin);
    $this->assertTrue($this->didLoad);
    $this->assertTrue($this->didReady);
  }

  function test_it_can_run_callback_on_plugin_load() {
    $plugin = array(
      'file' => 'my-plugin',
      'callback' => array($this, 'pluginCallback')
    );

    $this->loader->loadPlugin($plugin);
    $this->assertTrue($this->didCallback);
  }

  function test_it_can_load_plugins_in_correct_order() {
    $callback = array($this, 'pluginCallback');
    $this->loader->register($this->one, '2.0.5', $callback);
    $this->loader->register($this->two, '2.7.1', $callback);
    $this->loader->register($this->three, '2.6.2', $callback);

    $this->loader->load();

    $expected = array('one', 'three', 'two');

    $this->assertEquals($expected, $GLOBALS['arrowPlugins']);
    $this->assertEquals($expected, $this->pluginNames);
  }

  function test_it_is_a_singleton() {
    $instance1 = ArrowPluginLoader::getInstance();
    $instance2 = ArrowPluginLoader::getInstance();

    $this->assertSame($instance1, $instance2);
    ArrowPluginLoader::$instance = null;
  }

  function test_it_can_automatically_load_plugins_in_correct_order() {
    $this->loader = ArrowPluginLoader::getInstance();
    $callback = array($this, 'pluginCallback');
    $this->loader->register($this->one, '3.0.5', $callback);
    $this->loader->register($this->two, '3.7.1', $callback);
    $this->loader->register($this->three, '3.6.2', $callback);

    do_action('plugins_loaded');

    $expected = array('one', 'three', 'two');

    $this->assertEquals($expected, $GLOBALS['arrowPlugins']);
    $this->assertEquals($expected, $this->pluginNames);
  }

}
