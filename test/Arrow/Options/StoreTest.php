<?php

namespace Arrow\Options;

use Encase\Container;

class StoreTest extends \WP_UnitTestCase {

  public $container;
  public $store;
  public $pluginMeta;

  function setUp() {
    parent::setUp();

    $this->pluginMeta = new \Arrow\PluginMeta('store-plugin/store-plugin.php');
    $this->pluginMeta->defaultOptions = array(
      'foo' => 1,
      'bar' => 'two'
    );

    $container = new Container();
    $container
      ->object('pluginMeta', $this->pluginMeta)
      ->factory('store', 'Arrow\Options\Store');

    $this->container = $container;
    $this->store = $container->lookup('store');
  }

  function tearDown() {
    delete_option('store-plugin-options');
    parent::tearDown();
  }

  function test_it_has_plugin_meta() {
    $this->assertSame($this->pluginMeta, $this->store->pluginMeta);
  }

  function test_it_is_not_loaded_initially() {
    $this->assertFalse($this->store->loaded());
  }

  function test_it_can_convert_options_array_to_json() {
    $options = array('foo' => 'bar');
    $json = $this->store->toJSON($options);
    $this->assertEquals('{"foo":"bar"}', $json);
  }

  function test_it_can_convert_json_to_options_array() {
    $json = '{"foo":"bar"}';
    $options = $this->store->toOptions($json);
    $this->assertEquals(array('foo' => 'bar'), $options);
  }

  function test_it_can_pick_up_default_options_from_plugin_meta() {
    $options = $this->store->getDefaultOptions();
    $this->assertEquals($this->pluginMeta->defaultOptions, $options);
  }

  function test_it_can_pick_up_option_key_from_plugin_meta() {
    $actual = $this->store->getOptionsKey();
    $this->assertEquals($this->pluginMeta->optionsKey, $actual);
  }

  function test_it_can_parse_options_stored_as_json() {
    $json = '{"foo":"bar"}';
    $actual = $this->store->parse($json);
    $this->assertEquals('bar', $actual['foo']);
  }

  function test_it_can_parse_not_found_result_into_options() {
    $actual = $this->store->parse(false);
    $this->assertEquals($this->pluginMeta->defaultOptions, $actual);
  }

  function test_it_uses_default_options_if_json_parsing_fails() {
    $actual = $this->store->parse('{foo}');
    $this->assertEquals($this->pluginMeta->defaultOptions, $actual);
  }

  function test_it_knows_if_options_are_loaded() {
    update_option('store-plugin-options', '{"foo":1}');
    $this->store->load();
    $this->assertTrue($this->store->loaded());
  }

  function test_it_can_load_all_options_from_db() {
    $json = '{"lorem": 1, "ipsum": 2}';
    update_option('store-plugin-options', $json);

    $options = $this->store->getOptions();
    $this->assertEquals(1, $options['lorem']);
    $this->assertEquals(2, $options['ipsum']);
  }

  function test_it_can_load_specific_options_from_db() {
    $json = '{"lorem": 1, "ipsum": 2}';
    update_option('store-plugin-options', $json);

    $this->assertEquals(1, $this->store->getOption('lorem'));
    $this->assertEquals(2, $this->store->getOption('ipsum'));
  }

  function test_it_uses_default_option_if_not_found_in_db() {
    $json = '{"lorem": 1, "ipsum": 2}';
    update_option('store-plugin-options', $json);

    $this->assertEquals('two', $this->store->getOption('bar'));
  }

  function test_it_can_clear_all_options() {
    $json = '{"lorem": 1, "ipsum": 2}';
    update_option('store-plugin-options', $json);
    $this->store->load();
    $this->store->clear();

    $this->assertFalse(get_option('store-plugin-options'));
  }

  function test_it_can_change_options_on_non_loaded_store() {
    $this->store->setOption('a', 1);
    $this->store->setOption('b', 2);

    $this->store->save();

    $json = get_option('store-plugin-options');
    $actual = json_decode($json, true);

    $this->assertEquals(1, $actual['a']);
    $this->assertEquals(2, $actual['b']);
  }

  function test_it_can_change_options_on_loaded_store() {
    $json = '{"foo": 0, "bar": 0}';
    update_option('store-plugin-options', $json);

    $this->store->load();
    $this->store->setOption('foo', 1);
    $this->store->setOption('bar', 2);

    $this->store->save();
    $this->assertEquals('{"foo":1,"bar":2,"pluginVersion":"0.0.0"}', get_option('store-plugin-options'));
  }

  function test_it_can_change_options_in_memory() {
    $this->store->setOption('foo', 1);
    $this->store->setOption('bar', 2);

    $this->assertEquals(1, $this->store->getOption('foo'));
    $this->assertEquals(2, $this->store->getOption('bar'));
  }

  function test_it_wont_load_if_already_loaded() {
    $json = '{"foo":"one", "bar":"two"}';
    update_option('store-plugin-options', $json);
    $this->store->load();

    $json = '{"foo":"three", "bar":"two"}';
    update_option('store-plugin-options', $json);
    $this->store->load();

    $this->assertEquals('one', $this->store->getOption('foo'));
  }

  function test_it_returns_null_if_key_not_stored_and_not_in_defaults() {
    $json = '{"foo":"one", "bar":"two"}';
    update_option('store-plugin-options', $json);
    $this->store->load();

    $this->assertNull($this->store->getOption('unknown'));
  }

  function test_it_wont_overwrite_store_options_after_changes() {
    $json = '{"foo": "a1", "bar":"a2"}';
    update_option('store-plugin-options', $json);

    $this->store->setOption('foo', 'b1');
    $this->store->save();

    $this->store = $this->container->lookup('store');
    $actual = $this->store->getOption('bar');

    $this->assertEquals('a2', $actual);
  }

  function test_it_uses_default_options_when_setting_options_if_no_stored_options() {
    delete_option('store-plugin-options');
    $this->store->setOption('foo', 'z1');
    $this->store->setOption('custom1', 'y1');

    $this->store->save();

    $this->store = $this->container->lookup('store');
    $this->store->setOption('custom2', 'y2');

    $this->assertEquals('z1', $this->store->getOption('foo'));
    $this->assertEquals('two', $this->store->getOption('bar'));
    $this->assertEquals('y1', $this->store->getOption('custom1'));
    $this->assertEquals('y2', $this->store->getOption('custom2'));
  }

  function test_it_can_reload_store() {
    $this->store->setOption('foo', 100);
    $this->store->setOption('bar', 200);

    $json = '{"foo": 1000, "bar": 2000}';
    update_option('store-plugin-options', $json);

    $this->store->reload();

    $this->assertEquals(1000, $this->store->getOption('foo'));
    $this->assertEquals(2000, $this->store->getOption('bar'));
  }

  function test_it_wont_save_if_nothing_changed() {
    $json = '{"foo": 1000, "bar": 2000}';
    update_option('store-plugin-options', $json);

    $this->store->load();

    $this->assertFalse($this->store->save());
  }

  function test_it_will_save_after_changes_to_options() {
    $json = '{"foo": 1000, "bar": 2000}';
    update_option('store-plugin-options', $json);

    $this->store->setOption('foo', 'f1');

    $actual = $this->store->save();
    $this->assertTrue($actual);
    $this->assertEquals('f1', $this->store->getOption('foo'));
    $this->assertEquals(2000, $this->store->getOption('bar'));
  }

  function test_it_will_use_default_option_after_store_has_been_cleared() {
    $json = '{"foo": 1000, "bar": 2000}';
    update_option('store-plugin-options', $json);

    $this->store->clear();

    $this->assertEquals(1, $this->store->getOption('foo'));
    $this->assertEquals('two', $this->store->getOption('bar'));
  }

  function test_it_does_not_have_plugin_version_if_options_are_absent() {
    $this->store->clear();
    $this->store->reload();

    $this->assertNull($this->store->getOption('pluginVersion'));
  }

  function test_it_stores_plugin_version_on_save() {
    $this->store->setOption('foo', 'bar');
    $this->store->pluginMeta->version = '1.2.3';
    $this->store->save();

    $this->store->reload();
    $this->assertEquals('1.2.3', $this->store->getOption('pluginVersion'));
  }

}
