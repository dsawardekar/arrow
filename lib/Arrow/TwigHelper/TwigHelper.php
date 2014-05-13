<?php

namespace Arrow\TwigHelper;

use Arrow\TwigHelper\TwigReaper;

class TwigHelper {

  protected $baseDir    = null;
  protected $cacheDir   = null;
  protected $sourceDirs = array();
  protected $twigReaper = null;
  protected $options    = array();

  function setBaseDir($baseDir) {
    $this->baseDir = $baseDir;
    if (is_null($this->cacheDir)) {
      $this->cacheDir = "$baseDir/dist/templates";
    }

    if (count($this->sourceDirs) === 0) {
      $this->addSourceDir("$baseDir/templates");
    }
  }

  function getBaseDir() {
    return $this->baseDir;
  }

  function setOptions($options) {
    $this->options = $options;
  }

  function getOptions() {
    return $this->options;
  }

  function setCacheDir($cacheDir) {
    $this->cacheDir = $cacheDir;
  }

  function getCacheDir() {
    return $this->cacheDir;
  }

  function hasCacheDir() {
    return $this->cacheDir !== false && is_dir($this->cacheDir);
  }

  function getTwigOptions() {
    $options = $this->getOptions();
    if ($this->hasCacheDir()) {
      $options['cache'] = $this->getCacheDir();
    } else {
      $options['cache'] = false;
    }

    return $options;
  }

  function addSourceDir($dir) {
    array_push($this->sourceDirs, $dir);
  }

  function getSourceDirs() {
    return $this->sourceDirs;
  }

  function getTwigReaper() {
    if (is_null($this->twigReaper)) {
      $this->twigReaper = new TwigReaper();
      $this->twigReaper->setup($this->getSourceDirs(), $this->getTwigOptions());
    }

    return $this->twigReaper;
  }

  function getTwigEnvironment() {
    return $this->getTwigReaper()->getTwigEnvironment();
  }

  function getTemplateFile($template) {
    if (preg_match("/\\.twig$/", $template) === 0) {
      return "${template}.twig";
    } else {
      return $template;
    }
  }

  function render($template, $context = array()) {
    $templateFile = $this->getTemplateFile($template);
    $env          = $this->getTwigEnvironment();

    return $env->render($templateFile, $context);
  }

  function display($template, $context = array()) {
    $templateFile = $this->getTemplateFile($template);
    $env          = $this->getTwigEnvironment();

    return $env->display($templateFile, $context);
  }
}
