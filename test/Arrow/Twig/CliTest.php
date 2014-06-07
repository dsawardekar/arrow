<?php

namespace Arrow\Twig;

class CliTest extends \PHPUnit_Framework_TestCase {

  public $cli;

  function setUp() {
    parent::setUp();

    $this->cli = new Cli();
  }

  function test_it_has_a_container() {
    $this->assertInstanceOf('Encase\Container', $this->cli->container);
  }

  function test_it_has_a_twig_compiler() {
    $this->assertInstanceOf('Arrow\Twig\Compiler', $this->cli->lookup('twigCompiler'));
  }

  function test_it_can_compile_templates_from_sources_to_output_dir() {
    $this->cli->sourceDirs = array('test/templates');
    $this->cli->outputDir = 'test/dist/templates';

    $this->cli->run();
    // TODO: clean and verify
  }

}
