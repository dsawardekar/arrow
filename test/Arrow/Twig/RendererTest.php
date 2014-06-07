<?php

namespace Arrow\Twig;

use Encase\Container;

class RendererTest extends \PHPUnit_Framework_TestCase {

  public $container;
  public $twigEnv;
  public $renderer;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container
      ->object('twigEnvironment', $this->getTwigEnv())
      ->singleton('templateRenderer', 'Arrow\Twig\Renderer');

    $this->twigEnv = $this->container->lookup('twigEnvironment');
    $this->renderer = $this->container->lookup('templateRenderer');
  }

  function getTwigEnv() {
    return $this->getMock('Twig_Environment');
  }

  function setupTwigEnv() {
    $this->twigEnv
      ->expects($this->once())
      ->method('render')
      ->with(
        $this->equalTo('a-template'),
        $this->equalTo(array('foo' => 1))
      )
      ->will($this->returnValue('a-rendered-template'));
  }

  function test_it_has_a_twig_environment() {
    $this->assertSame($this->twigEnv, $this->renderer->twigEnvironment);
  }

  function test_it_can_render_template_using_twig_environment() {
    $this->setupTwigEnv();

    $actual = $this->renderer->render('a-template', array('foo' => 1));
    $this->assertEquals('a-rendered-template', $actual);
  }

  function test_it_can_display_template_using_twig_environment() {
    $this->setupTwigEnv();

    ob_start();
    $this->renderer->display('a-template', array('foo' => 1));
    $content = ob_get_clean();

    $this->assertEquals('a-rendered-template', $content);
  }

}
