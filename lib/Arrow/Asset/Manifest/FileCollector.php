<?php

namespace Arrow\Asset\Manifest;

class FileCollector {

  public $files = array();

  function collect($dir, $toCollect) {
    if (is_array($toCollect)) {
      $files = $this->sortFiles($dir, $toCollect);
      $this->files = array_merge($this->files, $files);
    } else {
      array_push($this->files, $toCollect);
    }
  }

  function getFiles() {
    return $this->files;
  }

  function reset() {
    $this->files = array();
  }

  function hasLoader($dir) {
    return file_exists($this->loaderPathFor($dir));
  }

  function loaderPathFor($dir) {
    return "$dir/.loader";
  }

  function loadOrderFor($dir) {
    if ($this->hasLoader($dir)) {
      return file($this->loaderPathFor($dir), FILE_IGNORE_NEW_LINES);
    } else {
      return array();
    }
  }

  function rankFiles($dir, $files) {
    $loadOrder = $this->loadOrderFor($dir);
    $ranks = array();
    $total = count($files);

    for ($i = 0; $i < $total; $i++) {
      $file     = $files[$i];
      $info     = pathinfo($file);
      $filename = $info['filename'];
      $result   = array_search($filename, $loadOrder);

      /* if file is ahead the load order, it's rank is higher */
      if ($result !== false) {
        $rank = 100000 - $result;
      } else {
        $rank = 10000 - $i;
      }

      $ranks[$rank] = $file;
    }

    return $ranks;
  }

  function sortFiles($dir, $files) {
    $ranks = $this->rankFiles($dir, $files);
    krsort($ranks);

    return array_values($ranks);
  }

}
