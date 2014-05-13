<?php

namespace Arrow\AssetManager;

class Asset {

  public $slug;
  public $options = array();
  public $dependencies = false;
  public $localizer = null;
  public $pluginMeta;

  /* abstract */
  public function needs() {
    return array('pluginMeta');
  }

  public function dirname() {
    return 'assets';
  }

  public function extension() {
    return '.js';
  }

  public function register() {

  }

  public function enqueue() {

  }

  public function localize($data) {

  }

  function runLocalizer() {
    $data = call_user_func($this->localizer, $this);
    $this->localize($data);
    return $data;
  }

  function localizeSlug() {
    $str = str_replace('-', '_', $this->slug);
    $str = str_replace('/', '_', $str);

    return $str;
  }

  function option($key) {
    if (array_key_exists($key, $this->options)) {
      $value = $this->options[$key];
    } else {
      $defaults = $this->defaultOptions();
      if (array_key_exists($key, $defaults)) {
        $value = $defaults[$key];
      } else {
        $value = false;
      }
    }

    return $value;
  }

  function relpath() {
    return $this->dirname() . "/" . $this->slug . $this->extension();
  }

  function path() {
    if ($this->isCustomSlug()) {
      return $this->customPath();
    } else {
      return plugins_url(
        $this->relpath(), $this->pluginMeta->getFile()
      );
    }
  }

  function isCustomSlug() {
    return preg_match('/^theme-/', $this->slug) === 1;
  }

  function underscorize($value) {
    return str_replace('_', '-', $value);
  }

  function customPath() {
    $slug = preg_replace('/^theme-/', '', $this->slug);
    $themeUrl = get_stylesheet_directory_uri();

    $path  = $themeUrl;
    $path .= '/';
    $path .= $this->underscorize($this->pluginMeta->getSlug());
    $path .= '/';
    $path .= $slug;
    $path .= $this->extension();

    return $path;
  }

  function defaultOptions() {
    return array();
  }

}
