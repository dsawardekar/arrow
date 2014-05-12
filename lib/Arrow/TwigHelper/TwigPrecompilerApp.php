<?php

namespace Arrow\TwigHelper;

use Arrow\TwigHelper\TwigPrecompiler;
use Arrow\TwigHelper\TwigReaper;

class TwigPrecompilerApp {

  public $opts   = null;
  public $reaper = null;

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
    $sources = $this->opts['s'];
    return explode(',', $sources);
  }

  function getTargetDir() {
    return $this->opts['t'];
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

