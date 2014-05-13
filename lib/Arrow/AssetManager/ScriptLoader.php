<?php

namespace Arrow\AssetManager;

class ScriptLoader extends AssetLoader {

  public function assetType() {
    return 'script';
  }

  function enqueueAction() {
    return 'wp_enqueue_scripts';
  }

}
