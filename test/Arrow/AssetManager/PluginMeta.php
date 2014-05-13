<?php

namespace Arrow\AssetManager;

class PluginMeta {

  public $file;
  public $slug;
  public $dir;
  public $scriptOptions;
  public $stylesheetOptions;

  function getFile() {
    return $this->file;
  }

  function getSlug() {
    return $this->slug;
  }

  function getDir() {
    return $this->dir;
  }

  function getScriptOptions() {
    return $this->scriptOptions;
  }

  function getStylesheetOptions() {
    return $this->stylesheetOptions;
  }

}
