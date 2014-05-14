<?php

namespace Arrow\OptionsManager;

class OptionsManager {

  function __construct($container) {
    $container
      ->singleton('optionsStore', 'Arrow\OptionsManager\OptionsStore')
      ->singleton('optionsFlash', 'Arrow\OptionsManager\OptionsFlash')
      ->singleton('optionsPostHandler', 'Arrow\OptionsManager\OptionsPostHandler')
      ->singleton('twigHelper', 'Arrow\TwigHelper\TwigHelper');

    $container->initializer('twigHelper', array($this, 'initializeTwig'));

    CustomValitronRules::load();
  }

  function initializeTwig($twigHelper, $container) {
    $pluginMeta = $container->lookup('pluginMeta');
    $twigHelper->setBaseDir($pluginMeta->getDir());
  }

}
