<?php

namespace Arrow\Twig;

use Encase\Container;

class PackagerTest extends \WP_UnitTestCase {

  public $container;
  public $pluginMeta;
  public $twigOptions;
  public $twigLoader;
  public $twigEnv;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container->object('pluginMeta', new \Arrow\PluginMeta('test/my-plugin.php'));

    $this->pluginMeta  = $this->container->lookup('pluginMeta');
    //$this->twigOptions = $this->container->lookup('twigOptions');
    //$this->twigLoader  = $this->container->lookup('twigLoader');
    //$this->twigEnv     = $this->container->lookup('twigEnvironment');
  }

  function setUpPackager() {
    $this->container->packager('twigPackager', 'Arrow\Twig\Packager');
    $this->packager = $this->container->lookup('twigPackager');
  }

  function test_it_knows_if_dir_does_not_exist() {
    $packager = new Packager();
    $this->assertFalse($packager->dirExists('foo'));
    $this->assertFalse($packager->dirExists(false));
  }

  function test_it_knows_path_to_templates_dir() {
    $this->setUpPackager();
    $actual = $this->packager->getTemplatesDir();
    $this->assertEquals('test/templates', $actual);
  }

  function test_it_knows_path_to_templates_cache_dir() {
    $this->setUpPackager();
    $actual = $this->packager->getCacheDir();
    $this->assertEquals('test/dist/templates', $actual);
  }

  function test_it_knows_path_to_custom_templates_dir() {
    $this->setUpPackager();
    $actual = $this->packager->getCustomTemplatesDir();
    $this->assertStringEndsWith('my-plugin/templates', $actual);
  }

  function test_it_knows_if_custom_templates_dir_is_absent() {
    $this->setUpPackager();
    $actual = $this->packager->hasCustomTemplatesDir();
    $this->assertFalse($actual);
  }

  function test_it_knows_if_custom_templates_dir_is_present() {
    $this->setUpPackager();
    $actual = $this->packager->hasCustomTemplatesDir();
    //$this->assertTrue($actual);
    // TODO: create and destroy this dir
  }

  function test_it_knows_path_to_cache_dir() {
    $this->setUpPackager();
    $actual = $this->packager->getCacheDir();
    $this->assertEquals('test/dist/templates', $actual);
  }

  function test_it_knows_path_to_custom_cache_dir() {
    $this->setUpPackager();
    $actual = $this->packager->getCustomCacheDir();
    $this->assertStringEndsWith('my-plugin/dist/templates', $actual);
  }

  function test_it_knows_if_custom_cache_dir_is_absent() {
    $this->setUpPackager();
    $actual = $this->packager->hasCustomCacheDir();
    $this->assertFalse($actual);
  }

  function test_it_uses_custom_cache_dir_path_if_present() {
    // TODO: mocking or manual create/destroy
  }

  function test_it_has_cache_disabled_if_cache_dir_is_absent() {
    $this->container->object('pluginMeta', new \Arrow\PluginMeta('unknown/unknown.php'));
    $this->setUpPackager();
    $actual = $this->packager->getCacheEnabled();

    $this->assertFalse($actual);
  }

  function test_it_has_cache_enabled_if_cache_dir_is_present() {
    $this->setUpPackager();
    $actual = $this->packager->getCacheEnabled();
    $this->assertTrue($actual);
  }

  function test_it_has_list_of_template_dirs() {
    $this->setUpPackager();
    $actual = $this->packager->getTemplateDirs();
    $this->assertEquals(array('test/templates'), $actual);
  }

  function test_it_disables_twig_cache_if_not_enabled() {
    $this->container->object('pluginMeta', new \Arrow\PluginMeta('unknown/unknown.php'));
    $this->setUpPackager();
    $actual = $this->packager->getTwigOptions();
    $this->assertFalse($actual['cache']);
  }

  function test_it_enables_twig_cache_if_enabled() {
    $this->setUpPackager();
    $actual = $this->packager->getTwigOptions();
    $this->assertEquals('test/dist/templates', $actual['cache']);
  }

  function test_it_creates_twig_loader_with_template_dirs() {
    $this->setUpPackager();
    $loader = $this->packager->getTwigLoader($this->container);
    $actual = $loader->getPaths();
    $this->assertEquals($this->packager->getTemplateDirs(), $actual);
  }

  function test_it_creates_twig_environment_with_template_loader() {
    $this->setUpPackager();
    $env = $this->packager->getTwigEnvironment($this->container);
    $actual = $env->getLoader();
    $this->assertSame($this->container->lookup('twigLoader'), $actual);
  }

  function test_it_creates_twig_environment_with_twig_options() {
    $this->setUpPackager();
    $this->container->object('twigOptions', array('cache' => 'foo'));
    $env = $this->packager->getTwigEnvironment($this->container);

    $this->assertEquals('foo', $env->getCache());
  }

  function test_it_registers_twig_renderer() {
    $this->setUpPackager();
    $renderer = $this->container->lookup('templateRenderer');
    $this->assertInstanceOf('Arrow\Twig\Renderer', $renderer);
  }

  /* integration tests */
  function test_it_can_render_template() {
    $this->setUpPackager();
    $renderer = $this->container->lookup('templateRenderer');

    $output = $renderer->render('hello.twig', array('name' => 'Darshan'));
    $this->assertEquals('Hello Darshan', $output);
  }

  function test_it_can_display_template() {
    $this->setUpPackager();
    $renderer = $this->container->lookup('templateRenderer');

    ob_start();
    $renderer->display('hello.twig', array('name' => 'Darshan'));
    $output = ob_get_clean();

    $this->assertEquals('Hello Darshan', $output);
  }

}
