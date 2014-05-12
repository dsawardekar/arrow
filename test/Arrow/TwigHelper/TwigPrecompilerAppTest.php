<?php

namespace Arrow\TwigHelper;

class TwigPrecompilerAppTest extends \PHPUnit_Framework_TestCase {

  public $app;

  function setUp() {
    $this->app = new TwigPrecompilerApp();
  }

  function test_it_can_build_list_of_source_dirs() {
    $this->app->opts = array( 's' => 'foo,bar');
    $actual = $this->app->getSourceDirs();
    $this->assertEquals(array('foo', 'bar'), $actual);
  }

  function test_it_can_get_target_dir_opt() {
    $this->app->opts = array('t' => 'foo');
    $this->assertEquals('foo', $this->app->getTargetDir());
  }

  function test_it_can_compile_sources_to_target() {
    $sources = array('test/templates');
    $target  = 'dist/templates';

    $this->app->compile($sources, $target);

    $reaper = $this->app->reaper;
    $env = $reaper->getTwigEnvironment();

    $this->assertTrue(file_exists($env->getCacheFilename('hello.twig')));
    $this->assertTrue(file_exists($env->getCacheFilename('bye.twig')));
  }

  function test_it_can_precompile_templates() {
    $opts = array(
      's' => 'test/templates',
      't' => 'dist/templates'
    );

    $this->app->opts = $opts;
    $this->app->run();

    $reaper = $this->app->reaper;
    $env = $reaper->getTwigEnvironment();

    $this->assertTrue(file_exists($env->getCacheFilename('hello.twig')));
    $this->assertTrue(file_exists($env->getCacheFilename('bye.twig')));
  }


}
