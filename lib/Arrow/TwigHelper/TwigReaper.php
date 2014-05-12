<?php

namespace Arrow\TwigHelper;

use Twig_Loader_Chain;
use Twig_Loader_Filesystem;
use Twig_Environment;

class TwigReaper {

  protected $twigLoader = null;
  protected $twigEnv    = null;

  function setup($templateDirs, $twigOptions = array()) {
    $validTemplateDirs = $this->toValidTemplateDirs($templateDirs);
    $this->twigLoader  = new Twig_Loader_Filesystem($validTemplateDirs);
    $this->twigEnv     = new Twig_Environment($this->twigLoader, $twigOptions);
  }

  function getTwigLoader() {
    return $this->twigLoader;
  }

  function getTwigEnvironment() {
    return $this->twigEnv;
  }

  function toValidTemplateDirs($templateDirs) {
    return array_filter($templateDirs, array($this, 'isValidTemplateDir'));
  }

  function isValidTemplateDir($dir) {
    return is_dir($dir);
  }

}
