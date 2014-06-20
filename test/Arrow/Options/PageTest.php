<?php

namespace Arrow\Options;

use Encase\Container;

class PageTest extends \WP_UnitTestCase {

  public $container;
  public $pluginMeta;
  public $page;
  public $scriptLoader;
  public $stylesheetLoader;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container
      ->object('pluginMeta', new \Arrow\PluginMeta('test/plugins/sample/sample.php'))
      ->packager('assetPackager', 'Arrow\Asset\Packager')
      ->packager('manifestPackager', 'Arrow\Asset\Manifest\Packager')
      ->singleton('optionsStore', 'Arrow\Options\Store')
      ->singleton('optionsPage', 'Arrow\Options\Page');

    $this->pluginMeta       = $this->container->lookup('pluginMeta');
    $this->page             = $this->container->lookup('optionsPage');
    $this->store            = $this->container->lookup('optionsStore');
    $this->scriptLoader     = $this->container->lookup('adminScriptLoader');
    $this->stylesheetLoader = $this->container->lookup('adminStylesheetLoader');
  }

  function test_it_has_plugin_meta() {
    $this->assertSame($this->pluginMeta, $this->page->pluginMeta);
  }

  function test_it_has_template_name() {
    $this->assertEquals('options', $this->page->getTemplateName());
  }

  function test_it_has_template_path() {
    $actual = $this->page->getTemplatePath();
    $this->assertEquals('test/plugins/sample/templates/options.html', $actual);
  }

  function test_it_has_new_nonce_value() {
    $nonce = $this->page->getNonceValue();
    $this->assertEquals(1, wp_verify_nonce($nonce, $this->pluginMeta->getSlug()));
  }

  function test_it_has_api_endpoint() {
    $apiEndpoint = $this->page->getApiEndpoint();
    $this->assertContains('admin-ajax.php', $apiEndpoint);
    $this->assertContains('action=sample', $apiEndpoint);
    $this->assertContains('admin=1', $apiEndpoint);
  }

  function test_it_has_page_context() {
    $config      = $this->page->getPageContext(null);
    $apiEndpoint = $config['apiEndpoint'];
    $nonce       = $config['nonce'];

    $this->assertEquals(1, wp_verify_nonce($nonce, $this->pluginMeta->getSlug()));
    $this->assertContains('admin-ajax.php', $apiEndpoint);
    $this->assertContains('action=sample', $apiEndpoint);
    $this->assertContains('admin=1', $apiEndpoint);
  }

  function test_it_can_add_options_page() {
    $this->page->register();
    $this->assertTrue($this->scriptLoader->isScheduled('app/models/a'));
    $this->assertTrue($this->stylesheetLoader->isScheduled('app/styles/a'));
  }

  function test_it_can_be_auto_registered() {
    $this->container->singleton('optionsPage', 'Arrow\Options\Page');
    $this->container->initializer('optionsPage', array($this, 'onPageInit'));
    $page = $this->container->lookup('optionsPage');
    $this->assertTrue($this->scriptLoader->isScheduled('app/models/a'));
  }

  function onPageInit($page, $container) {
    $page->enable();
    do_action('admin_menu');
  }

  function test_it_can_render_template() {
    ob_start();
    $this->page->show();
    $html = ob_get_clean();

    $this->assertContains('<p>options.html</p>', $html);
    $this->assertContains("data-template-name='application'", $html);
    $this->assertContains("data-template-name='posts'", $html);
    $this->assertContains("data-template-name='comments'", $html);
    $this->assertContains("data-template-name='posts/_partial'", $html);
  }

  function test_it_wont_enable_if_already_enabled() {
    $this->page->enable();
    $this->page->didEnable = 'already_enabled';
    $this->page->enable();

    $this->assertEquals('already_enabled', $this->page->didEnable);
  }

}
