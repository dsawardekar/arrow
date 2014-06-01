<?php

namespace Arrow\Ember;

class OptionsController extends \Arrow\Ajax\Controller {

  public $optionsStore;

  function needs() {
    return array_merge(
      parent::needs(),
      array('pluginMeta', 'optionsStore', 'optionsValidator')
    );
  }

  function index() {
    $this->sendSuccess($this->optionsStore->getOptions());
  }

  function update() {
    $valid = $this->optionsValidator->validate($this->params);
    if (!$valid) {
      $this->sendError($this->optionsValidator->errors(), 422);
      return;
    }

    $options = $this->pluginMeta->getDefaultOptions();
    foreach ($options as $key => $value) {
      $this->optionsStore->setOption(
        $key, $this->params[$key]
      );
    }

    $this->optionsStore->save();
    $this->index();
  }

  function delete() {
    $this->optionsStore->clear();
    $this->index();
  }

}
