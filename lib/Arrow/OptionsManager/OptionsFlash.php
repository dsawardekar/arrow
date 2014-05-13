<?php

namespace Arrow\OptionsManager;

class OptionsFlash {

  public $container;
  public $pluginMeta;

  protected $didLoad = false;
  protected $value = null;
  protected $flashKey = null;

  function needs() {
    return array('pluginMeta');
  }

  function keyName() {
    if (!is_null($this->flashKey)) {
      return $this->flashKey;
    }
    $optionsKey = $this->pluginMeta->getOptionsKey();
    $userID = get_current_user_id();

    $this->flashKey = "$optionsKey-flash-$userID";
    return $this->flashKey;
  }

  function load() {
    if ($this->didLoad) {
      return;
    }

    $result        = get_transient($this->keyName());
    $this->value   = $this->parse($result);
    $this->didLoad = true;
  }

  function save($data) {
    $json = json_encode($data);
    set_transient($this->keyName(), $json, 30);
  }

  function clear() {
    delete_transient($this->keyName());
    $this->value = null;
    $this->didLoad = false;
  }

  function loadAndClear() {
    $this->load();
    $value = $this->value;
    $this->clear();

    return $value;
  }

  function exists() {
    $this->load();
    return $this->value !== false;
  }

  function getValue() {
    $this->load();
    return $this->value;
  }

  function parse($json) {
    if ($json !== false) {
      $value = json_decode($json, true);
    } else {
      $value = $json;
    }

    return $value;
  }

}
