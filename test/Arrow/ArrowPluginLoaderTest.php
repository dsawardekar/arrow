<?php

require_once __DIR__ . '/../../lib/Arrow/ArrowPluginLoader.php';

class ArrowPluginLoaderTest extends \WP_UnitTestCase {

  public $pluginMeta;
  public $bootstrap;
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

    $this->pluginMeta = new ArrowPluginMeta('my-plugin.php');
    $this->bootstrap = new ArrowPluginBootstrap($this->pluginMeta);
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

  function test_it_knows_if_plugin_is_not_registered() {
    $this->assertFalse($this->loader->isRegistered($this->bootstrap));
  }

  function test_it_knows_if_plugin_is_registered() {
    $this->loader->register($this->bootstrap);
    $this->assertTrue($this->loader->isRegistered($this->bootstrap));
  }

  function test_it_knows_if_plugin_a_is_greater_than_b() {
    $aMeta = new ArrowPluginMeta('a', array(
      'arrowVersion' => '0.2.0')
    );
    $aBootstrap = new ArrowPluginBootstrap($aMeta);

    $bMeta = new ArrowPluginMeta('b', array(
      'arrowVersion' => '0.1.0')
    );
    $bBootstrap = new ArrowPluginBootstrap($bMeta);

    $actual = $this->loader->comparePlugins($aBootstrap, $bBootstrap);
    $this->assertEquals(1, $actual);
  }

  function test_it_knows_if_plugin_a_is_less_than_b() {
    $aMeta = new ArrowPluginMeta('a', array(
      'arrowVersion' => '0.1.0')
    );
    $aBootstrap = new ArrowPluginBootstrap($aMeta);

    $bMeta = new ArrowPluginMeta('b', array(
      'arrowVersion' => '0.2.0')
    );
    $bBootstrap = new ArrowPluginBootstrap($bMeta);

    $actual = $this->loader->comparePlugins($aBootstrap, $bBootstrap);
    $this->assertEquals(-1, $actual);
  }

  function test_it_knows_if_plugin_a_equals_b() {
    $aMeta = new ArrowPluginMeta('a', array(
      'arrowVersion' => '0.1.0')
    );
    $aBootstrap = new ArrowPluginBootstrap($aMeta);

    $bMeta = new ArrowPluginMeta('b', array(
      'arrowVersion' => '0.1.0')
    );
    $bBootstrap = new ArrowPluginBootstrap($bMeta);

    $actual = $this->loader->comparePlugins($aBootstrap, $bBootstrap);
    $this->assertEquals(0, $actual);
  }

  function getKeys(&$items) {
    $keys = array();
    foreach ($items as $item) {
      array_push($keys, $item->getPluginMeta()->getFile());
    }

    return $keys;
  }

  function newBootstrap($file, $arrowVersion, $options = array()) {
    $options['arrowVersion'] = $arrowVersion;
    $pluginMeta = new ArrowPluginMeta($file, $options);
    $bootstrap = new ArrowPluginBootstrap($pluginMeta);

    return $bootstrap;
  }

  function test_it_can_sort_plugins_into_correct_order() {
    $this->loader->register($this->newBootstrap('a', '3.0'));
    $this->loader->register($this->newBootstrap('b', '2.0'));
    $this->loader->register($this->newBootstrap('c', '1.0'));

    $plugins = $this->loader->sortPlugins();
    $keys = $this->getKeys($plugins);

    $this->assertEquals(array('c', 'b', 'a'), $keys);
  }

  function test_it_can_sort_semver_plugins_into_correct_order() {
    $this->loader->register($this->newBootstrap('a', '1.0.5'));
    $this->loader->register($this->newBootstrap('b', '1.7.1'));
    $this->loader->register($this->newBootstrap('c', '1.6.2'));

    $plugins = $this->loader->sortPlugins();
    $keys = $this->getKeys($plugins);

    $this->assertEquals(array('a', 'c', 'b'), $keys);
  }


  function test_it_can_load_plugins_in_correct_order() {
    $options = array('plugin' => 'TestArrowPlugin');
    $this->loader->register($this->newBootstrap($this->one, '2.0.5', $options));
    $options = array('plugin' => 'TestArrowPlugin');
    $this->loader->register($this->newBootstrap($this->two, '2.7.1', $options));
    $options = array('plugin' => 'TestArrowPlugin');
    $this->loader->register($this->newBootstrap($this->three, '2.6.2', $options));

    $this->loader->loadPlugins();

    $expected = array('one', 'three', 'two');
    $this->assertEquals($expected, $GLOBALS['arrowPlugins']);
  }

  function test_it_is_a_singleton() {
    $instance1 = ArrowPluginLoader::getInstance();
    $instance2 = ArrowPluginLoader::getInstance();

    $this->assertSame($instance1, $instance2);
    ArrowPluginLoader::$instance = null;
  }

  function test_it_can_automatically_load_plugins_in_correct_order() {
    $options = array('plugin' => 'TestArrowPlugin');
    $this->loader->register($this->newBootstrap($this->one, '2.0.5', $options));
    $options = array('plugin' => 'TestArrowPlugin');
    $this->loader->register($this->newBootstrap($this->two, '2.7.1', $options));
    $options = array('plugin' => 'TestArrowPlugin');
    $this->loader->register($this->newBootstrap($this->three, '2.6.2', $options));

    do_action('plugins_loaded');

    $expected = array('one', 'three', 'two');
    $this->assertEquals($expected, $GLOBALS['arrowPlugins']);
  }

  function test_it_can_load_plugins_via_static_api() {
    $options = array('plugin' => 'TestArrowPlugin', 'arrowVersion' => '2.0.5');
    ArrowPluginLoader::load($this->one, $options);
    $options = array('plugin' => 'TestArrowPlugin', 'arrowVersion' => '2.7.1');
    ArrowPluginLoader::load($this->two, $options);
    $options = array('plugin' => 'TestArrowPlugin', 'arrowVersion' => '2.6.2');
    ArrowPluginLoader::load($this->three, $options);

    do_action('plugins_loaded');

    $expected = array('one', 'three', 'two');
    $this->assertEquals($expected, $GLOBALS['arrowPlugins']);
  }

  function test_it_loads_plugins_only_once() {
    ArrowPluginLoader::$instance = null;

    $options = array('plugin' => 'TestArrowPlugin', 'arrowVersion' => '2.0.5');
    ArrowPluginLoader::load($this->one, $options);

    do_action('plugins_loaded');
    do_action('plugins_loaded');

    $expected = array('one');
    $this->assertEquals($expected, $GLOBALS['arrowPlugins']);
    $this->assertTrue(ArrowPluginLoader::getInstance()->loaded);
  }

}

class TestArrowPlugin {

  static function create($file) {
    return new TestArrowPlugin($file);
  }

  public $file;

  function __construct($file) {
    $this->file = $file;
  }

  function enable() {

  }

}
