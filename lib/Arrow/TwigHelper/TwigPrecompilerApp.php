<?php

namespace Arrow\TwigHelper;

use Arrow\TwigHelper\TwigPrecompiler;
use Arrow\TwigHelper\TwigReaper;

class TwigPrecompilerApp {

  public $opts   = null;
  public $reaper = null;
  public $sources = null;
  public $targetDir = null;

  function run() {
    $this->loadOpts();
    $sources = $this->getSourceDirs();
    $target  = $this->getTargetDir();

    $this->compile($sources, $target);
  }

  function compile($sources, $target) {
    $twigOpts    = array( 'cache' => $target );

    $this->reaper = new TwigReaper();
    $this->reaper->setup($sources, $twigOpts);

    $env      = $this->reaper->getTwigEnvironment();
    $compiler = new TwigPrecompiler();
    $compiler->setEnvironment($env);

    $compiler->compile($sources);
  }

  function getSourceDirs() {
    if (is_null($this->sources)) {
      $sources = $this->opts['s'];
      $this->sources = explode(',', $sources);
    }

    return $this->sources;
  }

  function getTargetDir() {
    if (is_null($this->targetDir)) {
      $this->targetDir = $this->opts['t'];
    }

    return $this->targetDir;
  }

  function loadOpts() {
    if (!is_null($this->opts)) {
      return;
    }

    $opts = "";
    $opts .= "s:";
    $opts .= "t:";

    $this->opts = getopt($opts);
  }

}

