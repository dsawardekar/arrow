<?php

namespace Arrow\Options;

use Encase\Container;

class PackagerTest extends \WP_UnitTestCase {

  public $container;
  public $pluginMeta;
  public $packager;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container->object(
      'pluginMeta', new \Arrow\PluginMeta('options-packager.php')
    );

  }

  function test_it_adds_ajax_packager_if_absent() {
    $this->container->packager('optionsPackager', 'Arrow\Options\Packager');
    $this->assertTrue($this->container->contains('ajaxPackager'));
    $this->assertInstanceOf('Arrow\Ajax\Packager', $this->container->lookup('ajaxPackager'));
  }

  function test_it_does_not_add_ajax_packager_if_already_present() {
    $ajaxPackager = new \Arrow\Ajax\Packager();
    $this->container->object('ajaxPackager', $ajaxPackager);
    $this->container->inject($ajaxPackager);

    $this->container->packager('optionsPackager', 'Arrow\Options\Packager');
    $this->assertEquals($ajaxPackager, $this->container->lookup('ajaxPackager'));
  }

  function test_it_registers_options_store() {
    $this->container->packager('optionsPackager', 'Arrow\Options\Packager');
    $this->assertInstanceOf('Arrow\Options\Store', $this->container->lookup('optionsStore'));
  }

  function test_it_registers_options_validator() {
    $this->container->packager('optionsPackager', 'Arrow\Options\Packager');
    $this->assertInstanceOf('Arrow\Options\Validator', $this->container->lookup('optionsValidator'));
  }

  function test_it_registers_options_page() {
    $this->container->packager('optionsPackager', 'Arrow\Options\Packager');
    $this->assertInstanceOf('Arrow\Options\Page', $this->container->lookup('optionsPage'));
  }

  function test_it_registers_options_controller() {
    $this->container->packager('optionsPackager', 'Arrow\Options\Packager');
    $this->assertInstanceOf('Arrow\Options\Controller', $this->container->lookup('optionsController'));
  }

  function test_it_can_be_auto_enabled_by_default() {
    $this->container->packager('optionsPackager', 'Arrow\Options\Packager');
    $packager = $this->container->lookup('optionsPackager');
    $this->assertTrue($packager->getAutoEnable());
  }

  function it_does_not_allow_public_ajax_access_by_default() {
    $this->container->packager('optionsPackager', 'Arrow\Options\Packager');
    $packager = $this->container->lookup('optionsPackager');
    $this->assertFalse($packager->getAllowPublic());
  }

  function test_it_can_enable_ajax_router() {
    $this->container->packager('optionsPackager', 'Arrow\Options\Packager');
    $router = $this->container->lookup('ajaxRouter');
    $this->assertTrue($router->didEnable);
  }

  function test_it_can_enable_options_page() {
    $this->container->packager('optionsPackager', 'Arrow\Options\Packager');
    $page = $this->container->lookup('optionsPage');
    $this->assertTrue($page->didEnable);
  }

}
