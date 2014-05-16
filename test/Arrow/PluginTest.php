<?php

namespace Arrow;

class PluginTest extends \WP_UnitTestCase {

  public $plugin;

  function setUp() {
    parent::setUp();

    $this->plugin = new Plugin('my-plugin.php');
    Plugin::$instances = array();
  }

  function test_it_can_create_instance() {
    $instance = Plugin::create('foo.php');
    $this->assertInstanceOf('Arrow\Plugin', $instance);
  }

  function test_it_is_a_singleton() {
    $instance1 = Plugin::create('foo.php');
    $instance2 = Plugin::getInstance();

    $this->assertSame($instance1, $instance2);
  }

  function test_it_can_lookup_items_from_container() {
    $container = $this->plugin->container;
    $container->object('foo', 'bar');
    $this->assertEquals('bar', $this->plugin->lookup('foo'));
  }

  function test_it_can_be_extended() {
    $myPlugin = MyChildPlugin::create('bar.php');
    $this->assertInstanceOf('Arrow\MyChildPlugin', $myPlugin);
    $this->assertEquals('bar.php', $myPlugin->lookup('pluginFile'));
  }

  function test_its_subclass_is_a_singleton() {
    MyChildPlugin::create('bar.php');
    $myPlugin = MyChildPlugin::getInstance();

    $this->assertEquals('bar.php', $myPlugin->lookup('pluginFile'));
  }

  function test_it_can_have_multiple_subclasses() {
    $one = MyChildPlugin::create('one.php');
    $two = AnotherChildPlugin::create('two.php');

    $this->assertEquals('one.php', $one->lookup('pluginFile'));
    $this->assertEquals('two.php', $two->lookup('file'));
  }

}

class MyChildPlugin extends Plugin {

  function __construct($file) {
    parent::__construct($file);
    $this->container
      ->object('pluginFile', $file);
  }

}

class AnotherChildPlugin extends Plugin {

  function __construct($file) {
    parent::__construct($file);
    $this->container
      ->object('file', $file);
  }

}
