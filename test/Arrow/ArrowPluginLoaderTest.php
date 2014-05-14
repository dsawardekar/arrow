<?php

require_once __DIR__ . '/../../lib/Arrow/ArrowPluginLoader.php';

class ArrowPluginLoaderTest extends \WP_UnitTestCase {

  public $loader;
  public $didLoad     = false;
  public $didReady    = false;
  public $didCallback = false;
  public $pluginNames = array();

  function setUp() {
    parent::setUp();

    $this->loader = new ArrowPluginLoader();
  }

  function pluginLoaded($name) {
    $this->didLoad = true;
  }

  function pluginReady($name) {
    $this->didReady = true;
  }

  function pluginCallback($name) {
    $this->didCallback = true;
    array_push($this->pluginNames, $name);
  }

  function test_it_knows_if_plugin_is_not_registered() {
    $this->assertFalse($this->loader->isRegistered('my-plugin'));
  }

  function test_it_knows_if_plugin_is_registered() {
    $this->loader->register(
      'my-plugin', '0.1.0', array($this, 'onPluginLoad')
    );

    $this->assertTrue($this->loader->isRegistered('my-plugin'));
  }

  function test_it_knows_if_plugin_a_is_greater_than_b() {
    $a = array(
      'name' => 'a',
      'arrowVersion' => '0.2.0'
    );

    $b = array(
      'name' => 'b',
      'arrowVersion' => '0.1.0'
    );

    $actual = $this->loader->comparePlugins($a, $b);
    $this->assertEquals(1, $actual);
  }

  function test_it_knows_if_plugin_a_is_less_than_b() {
    $a = array(
      'name' => 'a',
      'arrowVersion' => '0.1.0'
    );

    $b = array(
      'name' => 'b',
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
      array_push($keys, $item['name']);
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

  function test_it_can_send_plugin_event() {
    add_action('arrow-plugin-my-plugin-loaded', array($this, 'pluginLoaded'));
    $this->loader->sendPluginEvent('my-plugin', 'loaded');
    $this->assertTrue($this->didLoad);
  }

  function test_it_sends_plugin_events_on_plugin_load() {
    $plugin = array(
      'name' => 'my-plugin',
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
      'name' => 'my-plugin',
      'callback' => array($this, 'pluginCallback')
    );

    $this->loader->loadPlugin($plugin);
    $this->assertTrue($this->didCallback);
  }

  function test_it_can_load_plugins_in_correct_order() {
    $callback = array($this, 'pluginCallback');
    $this->loader->register('a', '2.0.5', $callback);
    $this->loader->register('b', '2.7.1', $callback);
    $this->loader->register('c', '2.6.2', $callback);

    $this->loader->load();

    $this->assertEquals(array('a', 'c', 'b'), $this->pluginNames);
  }

  function test_it_is_a_singleton() {
    $instance1 = ArrowPluginLoader::getInstance();
    $instance2 = ArrowPluginLoader::getInstance();

    $this->assertSame($instance1, $instance2);
    ArrowPluginLoader::$instance = null;
  }

  function test_it_can_automatically_load_plugins_in_correct_order() {
    $loader = ArrowPluginLoader::getInstance();
    $callback = array($this, 'pluginCallback');
    $loader->register('a', '3.0.5', $callback);
    $loader->register('b', '3.7.1', $callback);
    $loader->register('c', '3.6.2', $callback);

    do_action('plugins_loaded');

    $this->assertEquals(array('a', 'c', 'b'), $this->pluginNames);
  }

}
