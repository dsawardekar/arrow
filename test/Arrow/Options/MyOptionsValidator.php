<?php

namespace Arrow\OptionsManager;

use Valitron\Validator;

class MyOptionsValidator extends OptionsValidator {

  public $didCustomRules  = false;
  public $myCheckedFields = array();
  public $rules           = array();

  function loadRules($validator) {
    $validator->rules($this->rules);
  }

  function loadCustomRules() {
    $this->didCustomRules = true;

    Validator::addRule('customRule', function($field, $value, $params) {
      return $value === 'foo';
    }, 'values must be foo');
  }

  function checkedFields() {
    return $this->myCheckedFields;
  }

}
