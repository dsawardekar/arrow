<?php

namespace Arrow\OptionsManager;

use Valitron\Validator;

class CustomValitronRules {

  static public $instance = null;
  static function load() {
    if (is_null(self::$instance)) {
      self::$instance = new CustomValitronRules();
      self::loadCustomRules();
    }
  }

  static function loadCustomRules() {
    Validator::addRule(
      'safeText', array(self::$instance, 'isSafeText')
    );
  }

  function isSafeText($field, $value, $params) {
    return sanitize_text_field($value) === $value;
  }

}
