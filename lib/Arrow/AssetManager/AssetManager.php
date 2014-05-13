<?php

namespace Arrow\AssetManager;

class AssetManager {

  function __construct($container) {
    $container
      ->factory('script', 'Arrow\AssetManager\Script')
      ->factory('stylesheet', 'Arrow\AssetManager\Stylesheet')

      ->singleton('scriptLoader', 'Arrow\AssetManager\ScriptLoader')
      ->singleton('stylesheetLoader', 'Arrow\AssetManager\StylesheetLoader')

      ->singleton('adminScriptLoader', 'Arrow\AssetManager\AdminScriptLoader')
      ->singleton('adminStylesheetLoader', 'Arrow\AssetManager\AdminStylesheetLoader');
  }

}
