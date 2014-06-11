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
      ->object('pluginMeta', new \Arrow\PluginMeta('test/my-plugin.php'))
      ->packager('assetPackager', 'Arrow\Asset\Packager')
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

  function test_it_has_options_store() {
    $this->assertSame($this->store, $this->page->optionsStore);
  }

  function test_it_has_admin_script_loader() {
    $this->assertSame($this->scriptLoader, $this->page->adminScriptLoader);
  }

  function test_it_has_admin_stylesheet_loader() {
    $this->assertSame($this->stylesheetLoader, $this->page->adminStylesheetLoader);
  }

  function test_it_can_add_options_page() {
    $this->page->register();
    $this->assertTrue($this->scriptLoader->isScheduled('my-plugin-app'));
  }

  function test_it_can_be_auto_registered() {
    $this->container->singleton('optionsPage', 'Arrow\Options\Page');
    $this->container->initializer('optionsPage', array($this, 'onPageInit'));
    $page = $this->container->lookup('optionsPage');
    $this->assertTrue($this->scriptLoader->isScheduled('my-plugin-app'));
  }

  function onPageInit($page, $container) {
    $page->enable();
    do_action('admin_menu');
  }

  function test_it_has_template_name() {
    $this->assertEquals('options', $this->page->getTemplateName());
  }

  function test_it_has_template_path() {
    $actual = $this->page->getTemplatePath();
    $this->assertEquals('test/templates/options.html', $actual);
  }

  function test_it_has_new_nonce_value() {
    $nonce = $this->page->getNonceValue();
    $this->assertEquals(1, wp_verify_nonce($nonce, $this->pluginMeta->getSlug()));
  }

  function test_it_has_api_endpoint() {
    $apiEndpoint = $this->page->getApiEndpoint();
    $this->assertContains('admin-ajax.php', $apiEndpoint);
    $this->assertContains('action=my_plugin', $apiEndpoint);
    $this->assertContains('admin=1', $apiEndpoint);
  }

  function test_it_has_page_context() {
    $config      = $this->page->getPageContext(null);
    $apiEndpoint = $config['apiEndpoint'];
    $nonce       = $config['nonce'];

    $this->assertEquals(1, wp_verify_nonce($nonce, $this->pluginMeta->getSlug()));
    $this->assertContains('admin-ajax.php', $apiEndpoint);
    $this->assertContains('action=my_plugin', $apiEndpoint);
    $this->assertContains('admin=1', $apiEndpoint);
  }

  function test_it_has_default_options_scripts() {
    $scripts = $this->page->getOptionsScripts();
    $this->assertEquals(
      array('handlebars', 'ember', 'ember-validations', 'ember-easyForm'),
      $scripts
    );
  }

  function test_it_has_default_options_styles() {
    $styles = $this->page->getOptionsStyles();
    $this->assertEmpty($styles);
  }

  function test_it_can_load_options_scripts_in_correct_order() {
    $this->page->loadScripts();
    $this->assertEquals(
      array('jquery'),
      $this->scriptLoader->find('handlebars')->dependencies
    );
    $this->assertEquals(
      array('handlebars'),
      $this->scriptLoader->find('ember')->dependencies
    );
    $this->assertEquals(
      array('ember'),
      $this->scriptLoader->find('ember-validations')->dependencies
    );
    $this->assertEquals(
      array('ember-validations'),
      $this->scriptLoader->find('ember-easyForm')->dependencies
    );
    $this->assertEquals(
      array('ember-easyForm'),
      $this->scriptLoader->find('my-plugin-app')->dependencies
    );
  }

  function test_it_can_load_options_scripts_without_parent() {
    $this->page->scheduleAssets(
      $this->scriptLoader,
      array(
        'foo',
        'bar'
      ),
      array()
    );

    $this->assertEquals(
      false,
      $this->scriptLoader->find('foo')->dependencies
    );
    $this->assertEquals(
      array('foo'),
      $this->scriptLoader->find('bar')->dependencies
    );

    $this->assertTrue($this->scriptLoader->isScheduled('foo'));
    $this->assertTrue($this->scriptLoader->isScheduled('bar'));
  }

  function test_it_can_load_options_scripts() {
    $this->page->loadScripts();
    $this->assertTrue($this->scriptLoader->isScheduled('handlebars'));
    $this->assertTrue($this->scriptLoader->isScheduled('ember'));
    $this->assertTrue($this->scriptLoader->isScheduled('ember-validations'));
    $this->assertTrue($this->scriptLoader->isScheduled('ember-easyForm'));
  }

  function test_it_can_load_options_styles() {
    $this->page->loadStyles();
    $this->assertTrue($this->stylesheetLoader->isScheduled('my-plugin-app'));
  }

  function test_it_can_render_template() {
    ob_start();
    $this->page->show();
    $html = ob_get_clean();

    $this->assertContains('<p>options.html</p>', $html);
  }

  function test_it_wont_enable_if_already_enabled() {
    $this->page->enable();
    $this->page->enable();

    $this->assertTrue($this->page->didEnable);
  }

}
