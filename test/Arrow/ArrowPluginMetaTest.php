<?php

require_once __DIR__ . '/../../lib/Arrow/ArrowPluginLoader.php';
require_once 'vendor/dsawardekar/wp-requirements/lib/Requirements.php';

class ArrowPluginMetaTest extends \WP_UnitTestCase {

  public $pluginMeta;

  function setUp() {
    parent::setUp();

    $this->pluginMeta = new ArrowPluginMeta('my-plugin.php');
  }

  function test_it_stores_path_to_plugin_file() {
    $this->assertEquals('my-plugin.php', $this->pluginMeta->getFile());
  }

  function test_it_stores_specified_options() {
    $options = array('foo' => 'bar');
    $this->pluginMeta = new ArrowPluginMeta('my-plugin.php', $options);
    $this->assertEquals($options, $this->pluginMeta->getOptions());
  }

  function test_it_uses_name_from_options_if_present() {
    $this->pluginMeta->options = array('name' => 'foo-plugin');
    $this->assertEquals('foo-plugin', $this->pluginMeta->getName());
  }

  function test_it_has_default_plugin_name_from_options() {
    $this->assertEquals('my-plugin', $this->pluginMeta->getName());
  }

  function test_it_uses_requirements_from_options_if_present() {
    $requirements = new WP_Modern_Requirements();
    $options = array('requirements' => $requirements);
    $this->pluginMeta->options = $options;

    $this->assertSame($requirements, $this->pluginMeta->getRequirements());
  }

  function test_it_has_default_min_requirements() {
    $actual = $this->pluginMeta->getRequirements();
    $this->assertInstanceOf('WP_Min_Requirements', $actual);
  }

  function test_it_uses_arrow_version_from_options() {
    $options = array('arrowVersion' => '0.7.0');
    $this->pluginMeta->options = $options;
    $actual = $this->pluginMeta->getArrowVersion();

    $this->assertEquals('0.7.0', $actual);
  }

  function test_it_has_default_arrow_version() {
    $this->assertEquals('0.6.0', $this->pluginMeta->getArrowVersion());
  }

  function test_it_uses_plugin_class_from_options() {
    $options = array('plugin' => 'Foo\MyPlugin');
    $this->pluginMeta->options = $options;
    $actual = $this->pluginMeta->getPlugin();

    $this->assertEquals('Foo\MyPlugin', $actual);
  }

}

