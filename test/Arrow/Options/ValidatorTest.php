<?php

namespace Arrow\Options;

require_once __DIR__ . '/MyOptionsValidator.php';

class ValidatorTest extends \WP_UnitTestCase {

  public $validator;

  function setUp() {
    parent::setUp();

    $this->validator = new MyOptionsValidator();
  }

  function test_it_knows_if_input_is_not_valid() {
    $rules = array(
        'required' => array(
            array('foo'),
            array('bar')
        ),
        'length' => array(
            array('foo', 3)
        )
    );

    $this->validator->rules = $rules;
    $result = $this->validator->validate(array());
    $this->assertFalse($result);
  }

  function test_it_knows_if_input_is_valid() {
    $rules = array(
        'required' => array(
            array('foo'),
            array('bar')
        ),
        'length' => array(
            array('foo', 3)
        )
    );

    $input = array(
      'foo' => 'lor',
      'bar' => 'yes'
    );

    $this->validator->rules = $rules;
    $result = $this->validator->validate($input);
    $this->assertTrue($result);
  }

  function test_it_has_errors_if_input_is_not_valid() {
    $input = array(
      'bar' => 'two'
    );

    $rules = array(
        'required' => array(
            array('foo'),
            array('bar')
        )
    );

    $this->validator->rules = $rules;
    $result = $this->validator->validate($input);
    $errors = $this->validator->errors();
    $this->assertEquals('Foo is required', $errors['foo'][0]);
  }

  function test_it_can_use_custom_rules() {
    $rules = array(
      'customRule' => array(
        array('foo')
      )
    );

    $this->validator->rules = $rules;
    $input = array('foo' => 'foo');
    $result = $this->validator->validate($input);
    $this->assertTrue($result);
  }

  function test_it_knows_text_with_tags_is_not_safe() {
    $actual = Validator::isSafeText('safeText', '<b>foo</b>', null);
    $this->assertFalse($actual);
  }

  function test_it_knows_text_with_script_tags_is_not_safe() {
    $actual = Validator::isSafeText('safeText', '<script>alert("hello");</script>', null);
    $this->assertFalse($actual);
  }

  function test_it_knows_plain_text_is_safe() {
    $actual = Validator::isSafeText('safeText', 'foo', null);
    $this->assertTrue($actual);
  }

  function test_it_can_validate_with_safe_text_rule_with_invalid_input() {
    Validator::loadStaticRules();
    $validator = new \Valitron\Validator(array('name' => '<b>Darshan</b>'));
    $validator->rule('safeText', 'name');

    $this->assertFalse($validator->validate());
  }

  function test_it_can_validate_with_safe_text_rule_with_valid_input() {
    Validator::loadStaticRules();
    $validator = new \Valitron\Validator(array('name' => 'Darshan'));
    $validator->rule('safeText', 'name');

    $this->assertTrue($validator->validate());
  }
}
