<?php

namespace Arrow\OptionsManager;

class OptionsPostHandler {

  public $container;
  public $pluginMeta;
  public $optionsFlash;
  public $optionsValidator;
  public $optionsStore;
  public $optionsSentry;

  public $redirectTo = '';
  public $didDeny    = false;
  public $didQuit    = false;
  public $denyReason = '';
  public $didEnable  = false;

  function needs() {
    return array(
      'pluginMeta',
      'optionsFlash',
      'optionsValidator',
      'optionsStore',
      'optionsSentry'
    );
  }

  function enable() {
    add_action('admin_post_' . $this->getPostAction(), array($this, 'process'));
    $this->didEnable = true;
  }

  function process() {
    if (!$this->authorize()) {
      return false;
    }

    if ($this->isResetRequest()) {
      $this->reset();
    } elseif ($this->validate() === true) {
      $this->save();
    }

    $this->redirect();
  }

  function authorize() {
    if (!$this->optionsSentry->authorize()) {
      $this->didDeny    = true;
      $this->denyReason = $this->optionsSentry->denyReason;
      $this->didQuit    = $this->optionsSentry->didQuit;

      return false;
    } else {
      return true;
    }
  }

  function getPostAction() {
    $action = $this->pluginMeta->getOptionsKey() . '_post';
    return str_replace('-', '_', $action);
  }

  function getNonceName() {
    return $this->optionsSentry->getNonceName();
  }

  function validate() {
    $valid = $this->optionsValidator->validate($_POST);

    if ($valid === true) {
      $this->saveSuccess();
    } else {
      $this->saveErrors($this->optionsValidator->errors());
    }

    return $valid;
  }

  function reset() {
    $this->optionsStore->clear();
    $this->saveSuccess();
  }

  function save() {
    $defaults = $this->pluginMeta->getDefaultOptions();
    $store    = $this->optionsStore;
    $changed  = false;

    /* only fields in default options are saved */
    /* default options is effectively a whitelist of valid keys */
    foreach ($defaults as $name => $value) {
      if (array_key_exists($name, $_POST) && is_bool($value)) {
        // checked fields that are present
        $store->setOption($name, $this->toBoolean($_POST[$name]));
        $changed = true;
      } elseif (array_key_exists($name, $_POST)) {
        $store->setOption($name, $_POST[$name]);
        $changed = true;
      } elseif (is_bool($value)) {
        // checked fields that are absent
        $store->setOption($name, false);
        $changed = true;
      }
    }

    if ($changed) {
      $store->save();
    }
  }

  function toBoolean($value) {
    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
  }

  function redirect() {
    $this->redirectTo = $this->pluginMeta->getOptionsUrl();

    if (!$this->optionsSentry->isPHPUnit()) {
      wp_redirect($this->redirectTo);
    }

    $this->quit();
  }

  function saveSuccess() {
    $json = array('success' => true);
    $this->optionsFlash->save($json);
  }

  function saveErrors($errors) {
    $json = array(
      'errors' => $errors,
      'inputs' => $this->getUserInputs()
    );

    $this->optionsFlash->save($json);
  }

  function getUserInputs() {
    $defaults = $this->pluginMeta->getDefaultOptions();
    $options = array();

    foreach ($defaults as $name => $value) {
      if (array_key_exists($name, $_POST)) {
        $options[$name] = $_POST[$name];
      }
    }

    return $options;
  }

  function isResetRequest() {
    return array_key_exists('reset', $_POST);
  }

  function isSubmitRequest() {
    return array_key_exists('submit', $_POST);
  }

  function quit() {
    $this->didQuit = true;

    if (!$this->optionsSentry->isPHPUnit()) {
      exit();
    }
  }
}
