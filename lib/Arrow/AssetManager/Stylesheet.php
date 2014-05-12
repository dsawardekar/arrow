<?php

namespace Arrow\AssetManager;

class Stylesheet extends Asset {

  public $options = array('media' => 'all');

  public function dirname() {
    return 'css';
  }

  public function extension() {
    return '.css';
  }

  public function register() {
    wp_register_style(
      $this->slug,
      $this->path(),
      $this->dependencies,
      $this->option('version'),
      $this->option('media')
    );
  }

  function enqueue() {
    wp_enqueue_style($this->slug);
  }

}
