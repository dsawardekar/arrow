<?php

namespace Arrow\OptionsManager;

require_once __DIR__ . '/PluginMeta.php';
use Encase\Container;

class OptionsManagerTest extends \WP_UnitTestCase {

  public $container;
  public $manager;
  public $pluginMeta;

  function setUp() {
    parent::setup();

    $this->pluginMeta = new PluginMeta();
    $this->pluginMeta->dir = getcwd() . '/test';

    $this->container = new Container();
    $this->container
      ->object('pluginMeta', $this->pluginMeta)
      ->object('optionsManager', new OptionsManager($this->container));

    $this->manager = $this->container->lookup('optionsManager');
  }

  function test_it_adds_an_options_store() {
    $this->assertTrue($this->container->contains('optionsStore'));
  }

  function test_it_adds_an_options_flash() {
    $this->assertTrue($this->container->contains('optionsFlash'));
  }

  function test_it_adds_an_options_post_handler() {
    $this->assertTrue($this->container->contains('optionsPostHandler'));
  }

  function test_adds_a_twig_helper() {
    $this->assertTrue($this->container->contains('twigHelper'));
  }

  function test_it_initializes_the_twig_helper() {
    $twigHelper = $this->container->lookup('twigHelper');
    $this->assertEquals(getcwd() . '/test', $twigHelper->getBaseDir());
  }

}
