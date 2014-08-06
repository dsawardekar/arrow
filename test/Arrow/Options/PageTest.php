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
      ->packager('optionsManifest', 'Arrow\Options\Manifest')
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

  function test_it_has_page_context_options() {
    $config      = $this->page->getPageContext(null);
    $config      = $config['options'];
    $apiEndpoint = $config['apiEndpoint'];
    $nonce       = $config['nonce'];

    $this->assertEquals(1, wp_verify_nonce($nonce, $this->pluginMeta->getSlug()));
    $this->assertContains('admin-ajax.php', $apiEndpoint);
    $this->assertContains('action=sample', $apiEndpoint);
    $this->assertContains('admin=1', $apiEndpoint);
    $this->assertTrue($config['debug']);
  }

  function test_it_has_localized_strings_for_page() {
    $strings = $this->page->getPageContext(null);

    $this->assertTrue(is_array($strings));
  }

  function test_it_does_not_have_debug_true_in_production() {
    $mockMeta = $this->getMock('Arrow\PluginMeta', array(), array('foo.php'));
    $mockMeta->expects($this->once())->method('getDebug')->will($this->returnValue(false));
    $mockMeta->expects($this->once())->method('getOptionsContext')->will($this->returnValue(array()));

    $this->page->pluginMeta = $mockMeta;
    $actual = $this->page->getPageContext(null);

    $this->assertFalse($actual['options']['debug']);
  }

  function test_it_uses_plugin_meta_options_context_if_present() {
    $context = array(
      'foo' => '123',
      'bar' => '456'
    );
    $mockMeta = $this->getMock('Arrow\PluginMeta', array(), array('foo.php'));
    $mockMeta->expects($this->once())->method('getDebug')->will($this->returnValue(false));
    $mockMeta->expects($this->once())->method('getOptionsContext')->will($this->returnValue($context));

    $this->page->pluginMeta = $mockMeta;
    $actual      = $this->page->getPageContext(null);
    $actual      = $actual['options'];

    $this->assertEquals('123', $actual['foo']);
    $this->assertEquals('456', $actual['bar']);
  }

  function test_it_can_add_options_page() {
    $this->page->register();

    $manifest = $this->container->lookup('optionsManifest');
    $this->assertNotNull($manifest->getContext());
  }

  function test_it_configures_manifest_context_on_register() {
    $this->page->register();
    $actual = is_callable($this->page->optionsManifest->getContext());

    $this->assertTrue($actual);
  }

  function test_it_can_be_auto_registered() {
    $this->container->singleton('optionsPage', 'Arrow\Options\Page');
    $this->container->initializer('optionsPage', array($this, 'onPageInit'));
    $page = $this->container->lookup('optionsPage');
    $this->assertTrue($this->scriptLoader->isScheduled('sample-app'));
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
  }

  function test_it_wont_enable_if_already_enabled() {
    $this->page->enable();
    $this->page->didEnable = 'already_enabled';
    $this->page->enable();

    $this->assertEquals('already_enabled', $this->page->didEnable);
  }

}
