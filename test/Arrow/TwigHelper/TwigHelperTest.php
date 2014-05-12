<?php

namespace Arrow\TwigHelper;

use Arrow\TwigHelper\TwigReaper;

class TwigHelperTest extends \PHPUnit_Framework_TestCase {

  public $helper;

  function setUp() {
    $this->helper = new TwigHelper();
  }

  function test_it_stores_base_dir() {
    $this->helper->setBaseDir('foo');
    $this->assertEquals('foo', $this->helper->getBaseDir());
  }

  function test_it_stores_twig_options() {
    $opts = array( 'debug' => true );
    $this->helper->setOptions($opts);
    $this->assertEquals($opts, $this->helper->getOptions());
  }

  function test_it_has_default_cache_dir() {
    $this->helper->setBaseDir('foo');
    $this->assertEquals('foo/dist/templates', $this->helper->getCacheDir());
  }

  function test_it_can_change_cache_dir() {
    $this->helper->setBaseDir('foo');
    $this->helper->setCacheDir('bar');
    $this->assertEquals('bar', $this->helper->getCacheDir());
  }

  function test_it_knows_if_cache_dir_exists() {
    $this->helper->setBaseDir(getcwd());
    $this->helper->setCacheDir('test');
    $actual = $this->helper->hasCacheDir();
    $this->assertTrue($actual);
  }

  function test_it_knows_if_cache_dir_does_not_exist() {
    $this->helper->setBaseDir(getcwd());
    $this->helper->setCacheDir('foo/bar');
    $actual = $this->helper->hasCacheDir();
    $this->assertFalse($actual);
  }

  function test_it_adds_cache_dir_to_twig_options() {
    $this->helper->setCacheDir('test');
    $opts = $this->helper->getTwigOptions();
    $this->assertEquals('test', $opts['cache']);
  }

  function test_it_disables_caching_if_cache_dir_is_absent() {
    $this->helper->setCacheDir('foo/bar');
    $opts = $this->helper->getTwigOptions();
    $this->assertFalse($opts['cache']);
  }

  function test_it_has_default_templates_dir() {
    $this->helper->setBaseDir('foo');
    $actual = $this->helper->getSourceDirs();

    $this->assertEquals(array('foo/templates'), $actual);
  }

  function test_it_can_add_custom_templates_dir() {
    $this->helper->setBaseDir('foo');
    $this->helper->addSourceDir('lorem/ipsum');
    $actual = $this->helper->getSourceDirs();

    $this->assertContains('lorem/ipsum', $actual);
  }

  function test_it_has_a_twig_reaper() {
    $this->helper->setBaseDir('test/templates');
    $reaper = $this->helper->getTwigReaper();

    $this->assertInstanceOf('Arrow\TwigHelper\TwigReaper', $reaper);
  }

  function test_it_adds_twig_suffix_if_needed() {
    $actual = $this->helper->getTemplateFile('foo');
    $this->assertEquals('foo.twig', $actual);
  }

  function test_it_does_not_add_twig_suffix_if_already_present() {
    $actual = $this->helper->getTemplateFile('foo.twig');
    $this->assertEquals('foo.twig', $actual);
  }

  function test_it_has_a_twig_environment() {
    $this->helper->setBaseDir('test/templates');
    $reaper = $this->helper->getTwigEnvironment();

    $this->assertInstanceOf('\\Twig_Environment', $reaper);
  }

  function test_it_can_render_template() {
    $this->helper->setBaseDir('test');
    $actual = $this->helper->render('hello', array('name' => 'Darshan'));
    $this->assertEquals('Hello Darshan', $actual);
  }

  function test_it_can_display_template() {
    $this->helper->setBaseDir('test');

    ob_start();
    $this->helper->display('hello', array('name' => 'Darshan'));
    $actual = ob_get_clean();

    $this->assertEquals('Hello Darshan', $actual);
  }

}
