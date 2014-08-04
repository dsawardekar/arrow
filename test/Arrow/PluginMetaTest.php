<?php

namespace Arrow;

use Encase\Container;

class PluginMetaTest extends \WP_UnitTestCase {

  public $container;
  public $meta;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container->object('pluginMeta', new PluginMeta(getcwd() . '/my-plugin.php'));

    $this->meta = $this->container->lookup('pluginMeta');
  }

  function test_it_has_a_container() {
    $this->assertSame($this->container, $this->meta->container);
  }

  function test_it_can_lookup_items_in_container() {
    $this->container->object('bar', 'foo');
    $this->assertEquals('foo', $this->meta->lookup('bar'));
  }

  function test_it_store_path_to_main_plugin_file() {
    $actual = $this->meta->getFile();
    $this->assertEquals(getcwd() . '/my-plugin.php', $actual);
  }

  function test_it_can_build_slug_from_file_name() {
    $actual = $this->meta->getSlug();
    $this->assertEquals('my-plugin', $actual);
  }

  function test_it_stores_directory_of_main_plugin_file() {
    $this->meta = new PluginMeta('foo/my-plugin.php');
    $actual = $this->meta->getDir();
    $this->assertEquals('foo', $actual);
  }

  function test_it_can_build_default_options_key() {
    $actual = $this->meta->getOptionsKey();
    $this->assertEquals('my-plugin-options', $actual);
  }

  function test_it_has_default_options_capability() {
    $actual = $this->meta->getOptionsCapability();
    $this->assertEquals('manage_options', $actual);
  }

  function test_it_has_default_display_name() {
    $actual = $this->meta->getDisplayName();
    $this->assertEquals('My Plugin', $actual);
  }

  function test_it_has_default_options_page_title() {
    $meta = new PluginMeta('wp-my-plugin/wp-my-plugin.php');
    $actual = $meta->getOptionsPageTitle();
    $this->assertEquals('WP My Plugin | Settings', $actual);
  }

  function test_it_has_options_menu_title() {
    $actual = $this->meta->getOptionsMenuTitle();
    $this->assertEquals('My Plugin', $actual);
  }

  function test_it_has_options_page_slug() {
    $actual = $this->meta->getOptionsPageSlug();
    $this->assertEquals('my-plugin', $actual);
  }

  function test_it_has_empty_default_options() {
    $this->assertEquals(array(), $this->meta->getDefaultOptions());
  }

  function test_it_has_default_options_url() {
    $actual = $this->meta->getOptionsUrl();
    $expected = 'options-general.php?page=my-plugin';
    $this->assertStringEndsWith($expected, $actual);
  }

  function test_it_has_a_default_version() {
    $actual = $this->meta->getVersion();
    $this->assertNotEquals('', $actual);
  }

  function test_it_has_default_script_options() {
    $options = $this->meta->getScriptOptions();
    $this->assertNotEquals('', $options['version']);
    $this->assertTrue($options['in_footer']);
  }

  function test_it_has_default_stylesheet_options() {
    $options = $this->meta->getStylesheetOptions();
    $this->assertNotEquals('', $options['version']);
    $this->assertEquals('all', $options['media']);
  }

  function test_it_can_build_path_to_custom_stylesheet() {
    $actual = $this->meta->getCustomStylesheet();
    $this->assertStringEndsWith('/my-plugin/custom.css', $actual);
  }

  function test_it_can_build_path_to_custom_named_stylesheet() {
    $actual = $this->meta->getCustomStylesheet('foo.css');
    $this->assertStringEndsWith('/my-plugin/foo.css', $actual);
  }

  function test_it_knows_if_custom_stylesheet_does_not_exists() {
    $this->assertFalse($this->meta->hasCustomStylesheet());
  }

  function test_it_does_not_minify_in_debug_mode() {
    $this->assertFalse($this->meta->getMinify());
  }

  function test_it_can_minify_if_enabled() {
    $this->meta->minify = true;
    $this->assertTrue($this->meta->getMinify());
  }

  function test_it_checks_files_before_switching_to_minified_version() {
    $this->assertTrue($this->meta->getMinifyChecks());
  }

  function test_it_does_not_check_files_exists_for_minification_if_disabled() {
    $this->meta->minifyChecks = false;
    $this->assertFalse($this->meta->getMinifyChecks());
  }

  function test_it_does_not_have_ajax_debug_enabled_by_default() {
    $this->assertFalse($this->meta->ajaxDebug);
  }

  function test_it_ignores_ajax_debug_flag_if_debugging_mode_is_off() {
    $mock = \Mockery::mock('Arrow\PluginMeta[getDebug]', array('my-plugin.php'));
    $mock->shouldReceive('getDebug')->withNoArgs()->andReturn(false);

    $this->assertFalse($mock->getAjaxDebug());
  }

  function test_it_uses_ajax_debug_flag_when_debugging_is_on() {
    $mock = \Mockery::mock('Arrow\PluginMeta[getDebug]', array('my-plugin.php'));
    $mock->shouldReceive('getDebug')->withNoArgs()->andReturn(true);

    $mock->ajaxDebug = false;
    $this->assertFalse($mock->getAjaxDebug());
  }

  function test_it_uses_ajax_debug_flag_when_debugging_is_off() {
    $mock = \Mockery::mock('Arrow\PluginMeta[getDebug]', array('my-plugin.php'));
    $mock->shouldReceive('getDebug')->withNoArgs()->andReturn(true);

    $mock->ajaxDebug = true;
    $this->assertTrue($mock->getAjaxDebug());
  }

  function test_it_has_empty_options_context_by_default() {
    $context = $this->meta->getOptionsContext();
    $this->assertEmpty($context);
  }

  function _test_it_can_store_localized_strings() {
    $this->meta->localizedStrings = array('foo' => 'bar');
    $actual = $this->meta->getLocalizedStrings();
    $this->assertEquals(array('foo' => 'bar'), $actual);
  }

  function test_it_has_a_valid_text_domain() {
    $actual = $this->meta->getTextDomain();
    $this->assertEquals('my-plugin', $actual);
  }

  function test_it_has_a_valid_languages_directory() {
    $actual = $this->meta->getLanguagesDir();
    $this->assertEquals('my-plugin/languages/', $actual);
  }

  function test_it_can_load_text_domain() {
    $actual = $this->meta->loadTextDomain();
    $this->assertTrue($this->meta->loadedTextDomain);
  }

  function test_it_will_not_load_text_domain_if_already_loaded() {
    $this->meta->translate('Foo');
    $this->meta->loadedTextDomain = 'already_loaded';

    $this->meta->translate('Foo');
    $this->assertEquals('already_loaded', $this->meta->loadedTextDomain);
  }

  function test_it_can_translate_single_string() {
    $this->meta->translate('Lorem');
    $actual = $this->meta->localizedStrings['Lorem'];
    $this->assertEquals('Lorem', $actual);
  }

  function test_it_can_translate_single_string_with_aliases() {
    $this->assertEquals('Lorem', $this->meta->_('Lorem'));
    $this->assertEquals('Lorem', $this->meta->t('Lorem'));
    $this->assertEquals('Lorem', $this->meta->__('Lorem'));
  }

  function test_it_can_return_localized_strings() {
    $this->container->object('pluginMeta', new PluginMetaA('my-plugin.php'));
    $this->meta = $this->container->lookup('pluginMeta');
    $actual = $this->meta->getLocalizedStrings();

    $this->assertTrue($this->meta->loadedTextDomain);
    $this->assertEquals('Lorem', $actual['Lorem']);
    $this->assertEquals('Ipsum', $actual['Ipsum']);
    $this->assertEquals('Dolor', $actual['Dolor']);
  }

}

class PluginMetaA extends PluginMeta {
  function localize() {
    $this->_('Lorem');
    $this->_('Ipsum');
    $this->_('Dolor');
  }
}
