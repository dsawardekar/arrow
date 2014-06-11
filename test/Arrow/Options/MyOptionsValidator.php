<?php

namespace Arrow\Options;

class MyOptionsValidator extends \Arrow\Options\Validator {

  public $didCustomRules  = false;
  public $myCheckedFields = array();
  public $rules           = array();

  function loadRules($validator) {
    $validator->rules($this->rules);
  }

  function loadCustomRules() {
    parent::loadCustomRules();
    $this->didCustomRules = true;

    \Valitron\Validator::addRule('customRule', function($field, $value, $params) {
      return $value === 'foo';
    }, 'values must be foo');
  }

  function checkedFields() {
    return $this->myCheckedFields;
  }

}
