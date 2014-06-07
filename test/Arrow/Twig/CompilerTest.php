<?php

namespace Arrow\Twig;

use Encase\Container;

class CompilerTest extends \WP_UnitTestCase {

  public $container;
  public $packager;
  public $compiler;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container
      ->packager('cliPackager', 'Arrow\Twig\CliPackager')
      ->singleton('twigCompiler', 'Arrow\Twig\Compiler');

    $this->packager = $this->container->lookup('cliPackager');
    $this->packager->setSourceDirs(array('test/templates'));
    $this->packager->setOutputDir('test/dist/templates');

    $this->compiler = $this->container->lookup('twigCompiler');
  }

  function test_it_has_a_twig_environment() {
    $this->assertInstanceOf('Twig_Environment', $this->compiler->twigEnvironment);
  }

  function test_it_can_build_glob_for_directory() {
    $actual = $this->compiler->globForDir('test/templates');
    $this->assertEquals('test/templates/*.twig', $actual);
  }

  function test_it_can_find_templates_in_directory() {
    $actual = $this->compiler->templatesInDir('test/templates');
    $this->assertContains('test/templates/hello.twig', $actual);
    $this->assertContains('test/templates/bye.twig', $actual);
  }

  function test_it_can_find_template_name_from_filepath() {
    $actual = $this->compiler->templateNameFor('test/templates/hello.twig');
    $this->assertEquals('hello.twig', $actual);
  }

  function test_it_can_find_template_names_in_directory() {
    $actual = $this->compiler->templateNamesInDir('test/templates');
    $this->assertContains('hello.twig', $actual);
    $this->assertContains('bye.twig', $actual);
  }

  // TODO: create/delete and verify
  function test_it_can_compile_template() {
    $this->compiler->compileTemplate('hello.twig');
  }

  function test_it_can_compile_directory_of_templates() {
    $this->compiler->compileDir('test/templates');
  }

  function test_it_can_compile_list_of_directories_of_templates() {
    $this->compiler->compile(array('test/templates', 'test/templates'));
  }

}
