<?php

namespace Arrow\Twig;

use Encase\Container;

class CliPackagerTest extends \WP_UnitTestCase {

  public $container;
  public $pluginMeta;
  public $packager;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container
      ->object('pluginMeta', array())
      ->packager('cliPackager', 'Arrow\Twig\CliPackager');

    $this->packager = $this->container->lookup('cliPackager');
  }

  function test_it_stores_source_dirs() {
    $this->packager->setSourceDirs(array('foo', 'bar'));
    $actual = $this->packager->getSourceDirs();
    $this->assertEquals(array('foo', 'bar'), $actual);
  }

  function test_it_stores_output_dir() {
    $this->packager->setOutputDir('foo');
    $actual = $this->packager->getOutputDir();
    $this->assertEquals('foo', $actual);
  }

  function test_it_has_cache_enabled() {
    $this->assertTrue($this->packager->getCacheEnabled());
  }

  function test_it_uses_output_dir_as_cache_dir() {
    $this->packager->setOutputDir('foo');
    $this->assertEquals('foo', $this->packager->getCacheDir());
  }

  function test_it_uses_source_dirs_as_output_dirs() {
    $this->packager->setSourceDirs(array('foo', 'bar'));
    $this->assertEquals(array('foo', 'bar'), $this->packager->getTemplateDirs());
  }

  function test_it_can_render_template_from_cli_config() {
    $this->packager->setSourceDirs(array('test/templates'));
    $this->packager->setOutputDir('test/dist/templates');

    $renderer = $this->container->lookup('templateRenderer');
    $output   = $renderer->render('hello.twig', array('name' => 'Darshan'));

    $this->assertEquals('Hello Darshan', $output);
  }

}
