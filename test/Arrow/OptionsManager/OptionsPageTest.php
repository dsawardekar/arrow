<?php

namespace Arrow\OptionsManager;

require_once __DIR__ . '/MyOptionsPage.php';

use Encase\Container;
use Arrow\TwigHelper\TwigHelper;

class OptionsPageTest extends \WP_UnitTestCase {

  public $container;
  public $pluginMeta;
  public $store;
  public $flash;
  public $handler;
  public $validator;
  public $page;
  public $twigHelper;

  function setUp() {
    parent::setUp();

    $this->pluginMeta = new PluginMeta();
    $this->pluginMeta->slug = 'options-page-plugin';
    $this->pluginMeta->optionsKey = 'options-page-plugin-options';
    $this->pluginMeta->optionsCapability = 'manage_options';
    $this->pluginMeta->optionsPageTitle = 'Options Page Plugin';
    $this->pluginMeta->optionsMenuTitle = 'Options Page Plugin';
    $this->pluginMeta->defaultOptions = array(
      'foo' => 'one',
      'bar' => 'two'
    );

    $this->container = new Container();
    $this->container
      ->object('pluginMeta', $this->pluginMeta)
      ->singleton('optionsStore', 'Arrow\OptionsManager\OptionsStore')
      ->singleton('optionsFlash', 'Arrow\OptionsManager\OptionsFlash')
      ->singleton('optionsPostHandler', 'Arrow\OptionsManager\OptionsPostHandler')
      ->singleton('optionsValidator', 'Arrow\OptionsManager\MyOptionsValidator')
      ->singleton('twigHelper', 'Arrow\TwigHelper\TwigHelper')
      ->singleton('optionsPage', 'Arrow\OptionsManager\MyOptionsPage');

    $this->store      = $this->lookup('optionsStore');
    $this->flash      = $this->lookup('optionsFlash');
    $this->handler    = $this->lookup('optionsPostHandler');
    $this->validator  = $this->lookup('optionsValidator');
    $this->page       = $this->lookup('optionsPage');
    $this->twigHelper = $this->lookup('twigHelper');

    $this->twigHelper->setBaseDir(getcwd() . '/test');
  }

  function tearDown() {
    $this->flash->clear();
  }

  function lookup($key) {
    return $this->container->lookup($key);
  }

  function test_it_has_plugin_meta() {
    $this->assertSame(
      $this->pluginMeta, $this->page->pluginMeta
    );
  }

  function test_it_has_options_store() {
    $this->assertSame(
      $this->store, $this->page->optionsStore
    );
  }

  function test_it_has_options_flash() {
    $this->assertSame(
      $this->flash, $this->page->optionsFlash
    );
  }

  function test_it_has_options_post_handler() {
    $this->assertSame(
      $this->handler, $this->page->optionsPostHandler
    );
  }

  function test_it_has_a_twig_helper() {
    $this->assertSame(
      $this->twigHelper, $this->page->twigHelper
    );
  }

  function test_it_can_add_options_page() {
    $this->page->registerOptionsPage();
    /* TODO: how to test this? */
  }

  function test_it_passes_nonce_field_to_page_template_context() {
    $context = $this->page->getPageTemplateContext();
    $nonceField = $context['nonceField'];

    $matcher = array(
      'tag' => 'input',
      'attributes' => array(
        'name' => $this->handler->getNonceName(),
      )
    );

    $this->assertTag($matcher, $nonceField);
  }

  function test_it_can_render_template() {
    $this->page->templateName = 'hello';
    $this->page->templateContext = array('name' => 'Darshan');

    ob_start();
    $this->page->show();
    $result = ob_get_clean();

    $this->assertEquals('Hello Darshan', $result);
  }

  function captureMessages() {
    ob_start();
    $this->page->showMessages();
    return ob_get_clean();
  }

  function test_it_can_notify_post_success() {
    $this->page->registerSuccess();
    $html = $this->captureMessages();

    $this->assertContains('Settings Updated', $html);
    $this->assertContains('updated', $html);
  }

  function test_it_can_notify_post_errors() {
    $errors = array(
      'name' => array(
        'Name is required'
      )
    );

    $this->page->registerErrors($errors);
    $html = $this->captureMessages();

    $this->assertContains('Name is required', $html);
  }

}
