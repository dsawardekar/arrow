<?php

namespace Arrow\Options;

class Manifest extends \Arrow\Asset\Manifest\Manifest {

  public $pluginMeta;

  function needs() {
    return array('pluginMeta');
  }

  function getScripts() {
    $slug  = $this->pluginMeta->getSlug();
    $slugs = $this->getAssetSlugs();
    array_push($slugs, $slug . '-app-run');

    return $this->getValidSlugs($slugs, 'js');
  }

  function getStyles() {
    $slugs = $this->getAssetSlugs();
    return $this->getValidSlugs($slugs, 'css');
  }

  /* templates are precompiled to js */
  function getTemplates() {
    return array();
  }

  function getScriptOptions() {
    return $this->pluginMeta->getScriptOptions();
  }

  function getStylesheetOptions() {
    return $this->pluginMeta->getStylesheetOptions();
  }

  function getLocalizerVariable() {
    $slug = $this->pluginMeta->getSlug();
    return str_replace('-', '_', $slug);
  }

  /* helpers */
  function getDebug() {
    return $this->pluginMeta->getDebug();
  }

  function getAssetSlugs() {
    $slug = $this->pluginMeta->getSlug();

    if ($this->getDebug() && $this->hasDevAssets()) {
      $prefix = $slug . '/dist/assets/';

      return array(
        $prefix . 'vendor',
        $prefix . $slug,
      );
    } else {
      return array(
        $slug . '-vendor',
        $slug . '-app'
      );
    }
  }

  function hasDevAssets() {
    $dir  = $this->pluginMeta->getDir();
    $slug = $this->pluginMeta->getSlug();

    return is_dir($dir . '/js/' . $slug . '/dist/assets');
  }

  function hasSlugAsset($slug, $type) {
    $dir  = $this->pluginMeta->getDir();
    $path = $dir . '/' . $type . '/' . $slug . '.' . $type;

    return file_exists($path);
  }

  function getValidSlugs($slugs, $type) {
    $validSlugs = array();
    foreach ($slugs as $slug) {
      if ($this->hasSlugAsset($slug, $type)) {
        $validSlugs[] = $slug;
      }
    }

    return $validSlugs;
  }

}
