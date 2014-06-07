<?php

namespace Arrow\Options;

class Validator {

  static $staticRulesLoaded = false;

  static function loadStaticRules() {
    if (self::$staticRulesLoaded) {
      return;
    }

    \Valitron\Validator::addRule(
      'safeText', array('Arrow\Options\Validator', 'isSafeText')
    );

    self::$staticRulesLoaded = true;
  }

  static function isSafeText($field, $value, $params) {
    return sanitize_text_field($value) === $value;
  }

  public $validator;
  public $options;
  public $pluginMeta;

  function needs() {
    return array('pluginMeta');
  }

  function build() {
    $this->loadCustomRules();

    $this->validator = new \Valitron\Validator($this->options);
    $this->loadRules($this->validator);
  }

  function validate($options = null) {
    if (is_null($options)) {
      $options = $_POST;
    }

    $this->options = $options;
    $this->build();

    return $this->validator->validate();
  }

  function errors() {
    return $this->validator->errors();
  }

  /* abstract */
  function loadRules($validator) {
    return;
  }

  function loadCustomRules() {
    if (!Validator::$staticRulesLoaded) {
      Validator::loadStaticRules();
    }
  }


}
