<?php

namespace Arrow\Ajax;

use Encase\Container;

class PackagerTest extends \PHPUnit_Framework_TestCase {

  public $container;
  public $pluginMeta;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container
      ->object('pluginMeta', new \Arrow\PluginMeta('packager-plugin.php'))
      ->packager('ajaxPackager', 'Arrow\Ajax\Packager');

    $this->pluginMeta = $this->container->lookup('pluginMeta');
  }

  function test_it_registers_json_printer() {
    $actual = $this->container->lookup('ajaxJsonPrinter');
    $this->assertInstanceOf('Arrow\Ajax\JsonPrinter', $actual);
  }

  function test_it_registers_ajax_sentry() {
    $actual = $this->container->lookup('ajaxSentry');
    $this->assertInstanceOf('Arrow\Ajax\Sentry', $actual);
  }

  function test_it_registers_ajax_router() {
    $actual = $this->container->lookup('ajaxRouter');
    $this->assertInstanceOf('Arrow\Ajax\Router', $actual);
  }

}
