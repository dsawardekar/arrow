<?php

namespace Arrow\OptionsManager;

require_once __DIR__ . '/PluginMeta.php';
use Encase\Container;

class OptionsFlashTest extends \WP_UnitTestCase {

  public $container;
  public $pluginMeta;
  public $flash;

  function setUp() {
    parent::setUp();

    $this->pluginMeta = new PluginMeta();
    $this->pluginMeta->optionsKey = 'options-flash-plugin';

    $container = new Container();
    $container
      ->object('pluginMeta', $this->pluginMeta)
      ->singleton('flash', 'Arrow\OptionsManager\OptionsFlash');

    $this->container = $container;
    $this->flash = $container->lookup('flash');
  }

  function test_it_has_plugin_meta() {
    $this->assertSame($this->pluginMeta, $this->flash->pluginMeta);
  }

  function test_it_has_key_name() {
    $userID = get_current_user_id();
    $actual = $this->flash->keyName();

    $this->assertEquals("options-flash-plugin-flash-$userID", $actual);
  }

  function test_it_knows_if_flash_key_does_not_exist() {
    $this->assertFalse($this->flash->exists());
  }

  function test_it_knows_if_flash_key_exists() {
    set_transient($this->flash->keyName(), 'foo');
    $this->assertTrue($this->flash->exists());
  }

  function test_it_can_parse_json_in_transient() {
    $json = '{"foo":"bar"}';
    $actual = $this->flash->parse($json);

    $this->assertEquals('bar', $actual['foo']);
  }

  function test_it_can_save_json_to_transient() {
    $data = array('foo' => 1);
    $this->flash->save($data);

    $this->assertEquals('{"foo":1}', get_transient($this->flash->keyName()));
  }

  function test_it_can_clear_flash_transient() {
    $this->flash->save(array('foo' => 1));
    $this->flash->clear();
    $this->assertFalse(get_transient($this->flash->keyName()));
  }

  function test_it_can_load_and_clear_transient_in_one_step() {
    $this->flash->save(array('lorem' => 'ipsum'));
    $actual = $this->flash->loadAndClear();

    $this->assertEquals('ipsum', $actual['lorem']);
    $this->assertFalse(get_transient($this->flash->keyName()));
  }

  function test_it_can_get_value_of_saved_flash() {
    $this->flash->save(array('lorem' => 'ipsum'));
    $value = $this->flash->getValue();

    $this->assertEquals('ipsum', $value['lorem']);
  }

}
