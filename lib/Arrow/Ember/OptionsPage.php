<?php

namespace Arrow\Ember;

class OptionsPage extends \Arrow\OptionsManager\OptionsPage {

  function needs() {
    return array('pluginMeta');
  }

  function getTemplatePath() {
    return $this->pluginMeta->getDir() . '/templates/' . $this->getTemplateName() . '.html';
  }

  function show() {
    include($this->getTemplatePath());
  }

}
