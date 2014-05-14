<?php

namespace Arrow\OptionsManager;

use Valitron\Validator;

class CustomValitronRulesTest extends \WP_UnitTestCase {

  public $rules;

  function setUp() {
    parent::setUp();

    $this->rules = new CustomValitronRules();
  }

  function test_it_knows_text_with_tags_is_not_safe() {
    $actual = $this->rules->isSafeText('safeText', '<b>foo</b>', null);
    $this->assertFalse($actual);
  }

  function test_it_knows_text_with_script_tags_is_not_safe() {
    $actual = $this->rules->isSafeText('safeText', '<script>alert("hello");</script>', null);
    $this->assertFalse($actual);
  }

  function test_it_knows_plain_text_is_safe() {
    $actual = $this->rules->isSafeText('safeText', 'foo', null);
    $this->assertTrue($actual);
  }

  function test_it_can_load_custom_valitron_rules() {
    CustomValitronRules::load();
    $this->assertNotNull(CustomValitronRules::$instance);

  }

  function test_it_can_validate_with_safe_text_rule_with_invalid_input() {
    CustomValitronRules::load();
    $validator = new Validator(array('name' => '<b>Darshan</b>'));
    $validator->rule('safeText', 'name');

    $this->assertFalse($validator->validate());
  }

  function test_it_can_validate_with_safe_text_rule_with_valid_input() {
    CustomValitronRules::load();
    $validator = new Validator(array('name' => 'Darshan'));
    $validator->rule('safeText', 'name');

    $this->assertTrue($validator->validate());
  }
}
