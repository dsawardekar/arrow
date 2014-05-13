<?php

namespace Arrow\TwigHelper;

class TwigReaperTest extends \PHPUnit_Framework_TestCase {

  public $reaper;

  function setUp() {
    $this->reaper = new TwigReaper();
  }

  function test_it_knows_if_template_directory_is_valid() {
    $actual = $this->reaper->isValidTemplateDir('test/templates');
    $this->assertTrue($actual);
  }

  function test_it_knows_if_template_directory_is_invalid() {
    $actual = $this->reaper->isValidTemplateDir('foo');
    $this->assertFalse($actual);
  }

  function test_it_removes_non_existing_dirs() {
    $sourceDirs = array(
      'foo/bar',
      'bar/foo',
      'test/templates'
    );

    $expected = array(
      'test/templates'
    );

    $actual = $this->reaper->toValidTemplateDirs($sourceDirs);
    $this->assertEquals(array_values($expected), array_values($actual));
  }

  function test_it_can_setup_twig_loader() {
    $dirs = array('test/templates');
    $this->reaper->setup($dirs);

    $loader = $this->reaper->getTwigLoader();
    $this->assertEquals($dirs, $loader->getPaths());
  }

  function test_it_can_setup_twig_environment() {
    $dirs = array('test/templates');
    $this->reaper->setup($dirs);

    $env = $this->reaper->getTwigEnvironment();
    $template = 'hello.twig';
    $actual = $env->render($template, array('name' => 'Darshan'));

    $this->assertEquals('Hello Darshan', $actual);
  }

}
