<?php

namespace Arrow\Ember;

use Encase\Container;

class OptionsPageTest extends \WP_UnitTestCase {

  public $container;
  public $pluginMeta;
  public $page;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container
      ->object('pluginMeta', new \Arrow\PluginMeta('my-plugin.php'))
      ->singleton('optionsPage', 'Arrow\Ember\OptionsPage');

    $this->pluginMeta = $this->container->lookup('pluginMeta');
    $this->page = $this->container->lookup('optionsPage');
  }

  function test_it_has_plugin_meta() {
    $this->assertSame($this->pluginMeta, $this->page->pluginMeta);
  }

  function test_it_has_template_path() {
    $actual = $this->page->getTemplatePath();
    $this->assertEquals('./templates/options.html', $actual);
  }

  function test_it_can_render_template() {
    ob_start();
    $this->page->show();
    $html = ob_get_clean();

    $this->assertContains('<p>options.html</p>', $html);
  }

}
