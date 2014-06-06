<?php

namespace Arrow\OptionsManager;

class OptionsStore {

  public $container;
  public $pluginMeta;

  protected $didLoad = false;
  protected $options;

  function needs() {
    return array('pluginMeta');
  }

  function loaded() {
    return $this->didLoad;
  }

  function load() {
    if ($this->loaded()) {
      return;
    }

    $json = get_option($this->getOptionsKey());
    $this->options = $this->parse($json);
    $this->didLoad = true;
  }

  function save() {
    $json = $this->toJSON($this->options);
    update_option($this->getOptionsKey(), $json);
  }

  function clear() {
    delete_option($this->getOptionsKey());

    $this->options = null;
    $this->didLoad = false;
  }

  function getOptions() {
    if (is_null($this->options)) {
      $this->load();
    }
    return $this->options;
  }

  function getOption($name) {
    if (is_null($this->options)) {
      $this->load();
    }

    if (array_key_exists($name, $this->options)) {
      $value = $this->options[$name];
    } else {
      $defaultOptions = $this->getDefaultOptions();
      if (array_key_exists($name, $defaultOptions)) {
        $value = $defaultOptions[$name];
      } else {
        $value = null;
      }
    }

    return $value;
  }

  function setOption($name, $value) {
    if (!$this->loaded() && is_null($this->options)) {
      $this->options = array();
    }

    $this->options[$name] = $value;
  }

  function parse($json) {
    if ($json !== false) {
      $options = $this->toOptions($json);
      if (is_null($options)) {
        $options = $this->getDefaultOptions();
      }
    } else {
      $options = $this->getDefaultOptions();
    }

    return $options;
  }

  function toJSON(&$options) {
    return json_encode($options);
  }

  function toOptions($json) {
    return json_decode($json, true);
  }

  function getDefaultOptions() {
    return $this->pluginMeta->getDefaultOptions();
  }

  function getOptionsKey() {
    return $this->pluginMeta->getOptionsKey();
  }

}
