<?php

require_once __DIR__ . '/../../lib/Arrow/ArrowPluginLoader.php';
//require_once 'vendor/dsawardekar/wp-requirements/lib/Requirements.php';

class ArrowPluginBootstrapTest extends \WP_UnitTestCase {

  public $pluginMeta;
  public $bootstrap;

  function setUp() {
    parent::setUp();

    $this->pluginMeta = new ArrowPluginMeta('foo-plugin.php');
    $this->bootstrap  = new ArrowPluginBootstrap($this->pluginMeta);

    $GLOBALS['arrowPlugins'] = array();
  }

  function test_it_stores_plugin_meta_specified() {
    $this->assertSame($this->pluginMeta, $this->bootstrap->getPluginMeta());
  }

  function test_it_can_require_file_specified() {
    $this->bootstrap->requireFile('test/plugins/one/vendor/autoload.php');
    $this->assertEquals(array('one'), $GLOBALS['arrowPlugins']);
  }

  function test_it_knows_path_to_requirements_library() {
    $actual = $this->bootstrap->getRequirementsPath();
    $this->assertStringEndsWith('vendor/dsawardekar/wp-requirements/lib/Requirements.php', $actual);
  }

  function test_it_can_load_requirements_library() {
    $this->bootstrap->loadRequirements();
    $this->assertTrue(class_exists('WP_Requirements'));
  }

  function test_it_knows_path_to_plugins_autoloader() {
    $this->pluginMeta->file = 'test/plugins/one/one.php';
    $actual = $this->bootstrap->getAutoloaderPath();

    $this->assertStringEndsWith('one/vendor/autoload.php', $actual);
  }

  function test_it_can_load_plugins_autoloader() {
    $this->pluginMeta->file = 'test/plugins/one/one.php';
    $this->bootstrap->autoload();

    $this->assertEquals(array('one'), $GLOBALS['arrowPlugins']);
  }

  function test_it_can_run_faux_plugin_without_errors_when_scraping() {
    $this->pluginMeta->requirements = new WP_Failing_Requirements();
    $this->pluginMeta->getRequirements()->satisfied();
    $plugin = $this->bootstrap->runFauxPlugin();

    $_GET['action'] = 'error_scrape';

    // manual trigger for testing
    ob_start();
    $plugin->onActivate();
    $html = ob_get_clean();

    $this->assertContains('error', $html);
  }

  function test_it_can_run_faux_plugin_with_errors_when_not_scraping() {
    $this->pluginMeta->requirements = new WP_Failing_Requirements();
    $this->pluginMeta->getRequirements()->satisfied();
    $plugin = $this->bootstrap->runFauxPlugin();

    $this->setExpectedException('WP_Requirements_Exception');
    $plugin->onActivate();
  }

  function test_it_can_send_plugin_events_as_actions() {
    add_action('arrow-plugin-wp-foo-ready', array($this, 'arrowPluginEventReceived'));
    $this->bootstrap->sendPluginEvent('wp-foo', 'ready');

    $this->assertTrue($this->didArrowPluginEvent);
  }

  function arrowPluginEventReceived() {
    $this->didArrowPluginEvent = true;
  }

  function test_it_can_register_self_with_plugin_loader() {
    $actual = ArrowPluginLoader::getInstance()->isRegistered($this->bootstrap);
    $this->assertFalse($actual);

    $this->bootstrap->register();
    $actual = ArrowPluginLoader::getInstance()->isRegistered($this->bootstrap);
    $this->assertTrue($actual);
  }

  function test_it_can_run_plugin_class() {
    add_action('arrow-plugin-foo-plugin-ready', array($this, 'arrowPluginEventReceived'));

    $options = array(
      'plugin' => 'MyArrowPlugin'
    );

    $this->pluginMeta->options = $options;
    $plugin = $this->bootstrap->run();

    $this->assertInstanceOf('MyArrowPlugin', $plugin);
    $this->assertEquals('foo-plugin.php', $plugin->file);
    $this->assertTrue($this->didArrowPluginEvent);
  }

  function test_it_loads_requirements_on_start() {
    $this->bootstrap->start();
    $this->assertTrue(class_exists('WP_Requirements'));
  }

  function test_it_runs_faux_plugin_if_requirements_are_not_satisfied() {
    $this->pluginMeta->requirements = new WP_Failing_Requirements();
    $this->bootstrap->start();

    $this->assertTrue($this->bootstrap->didFauxPlugin);
  }

  function test_it_register_bootstrap_with_loader_if_requirements_are_satisfied() {
    $this->bootstrap->start();
    $actual = ArrowPluginLoader::getInstance()->isRegistered($this->bootstrap);
    $this->assertTrue($actual);
  }

}

class MyArrowPlugin {

  static function create($file) {
    return new MyArrowPlugin($file);
  }

  public $file;

  function __construct($file) {
    $this->file = $file;
  }

  function enable() {

  }


}
