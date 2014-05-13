<?php

namespace Arrow\OptionsManager;

class OptionsPostHandler {

  public $container;
  public $pluginMeta;
  public $optionsFlash;
  public $optionsValidator;
  public $optionsStore;

  public $postAction = null;
  public $redirectTo = '';
  public $didDeny = false;
  public $didQuit = false;
  public $denyReason = '';
  public $didEnable = false;

  function needs() {
    return array(
      'pluginMeta',
      'optionsFlash',
      'optionsValidator',
      'optionsStore'
    );
  }

  function enable() {
    add_action('admin_post_' . $this->getPostAction(), array($this, 'process'));
    $this->didEnable = true;
  }

  function process() {
    if ($this->isPOST() === false) {
      return $this->deny('not_post');
    }

    if ($this->isValidReferer() === false) {
      return $this->deny('invalid_referer');
    }

    if ($this->isValidNonce() === false) {
      return $this->deny('invalid_nonce');
    }

    if ($this->isLoggedIn() === false) {
      return $this->deny('not_logged_in');
    }

    if ($this->hasOptionsAccess() === false) {
      return $this->deny('not_enough_permissions');
    }

    if ($this->isResetRequest()) {
      $this->reset();
    } elseif ($this->validate() === true) {
      $this->save();
    }

    $this->redirect();
  }

  function getPostAction() {
    if (!is_null($this->postAction)) {
      return $this->postAction;
    }

    $optionsKey       = $this->pluginMeta->getOptionsKey();
    $this->postAction = "$optionsKey-post";
    $this->postAction = str_replace('-', '_', $this->postAction);

    return $this->postAction;
  }

  function getNonceName() {
    $prefix = $this->getPostAction();
    $name   = $prefix . "_wpnonce";

    return str_replace('-', '_', $name);
  }

  function getNonceValue() {
    $key = $this->getNonceName();

    if (array_key_exists($key, $_POST)) {
      return $_POST[$key];
    } else {
      return '';
    }
  }

  function deny($reason = '') {
    $this->didDeny = true;
    $this->denyReason = $reason;

    if (!$this->isPHPUnit()) {
      wp_die('You do not have sufficient permissions to access this page.');
    }
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

    if (!$this->isPHPUnit()) {
      wp_redirect($this->redirectTo);
    }

    $this->quit();
  }

  function quit() {
    $this->didQuit = true;

    if (!$this->isPHPUnit()) {
      exit();
    }
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

  function isPOST() {
    return array_key_exists('REQUEST_METHOD', $_SERVER) && $_SERVER['REQUEST_METHOD'] === 'POST';
  }

  function isValidReferer() {
    return $this->getReferer() === $this->pluginMeta->getOptionsUrl();
  }

  function getReferer() {
    if (array_key_exists('HTTP_REFERER', $_SERVER)) {
      return $_SERVER['HTTP_REFERER'];
    } else {
      return '';
    }
  }

  function isValidNonce() {
    return wp_verify_nonce(
      $this->getNonceValue(), $this->getPostAction()
    ) !== false;
  }

  function isLoggedIn() {
    return is_user_logged_in();
  }

  function hasOptionsAccess() {
    $capability = $this->pluginMeta->getOptionsCapability();
    return current_user_can($capability);
  }

  function isPHPUnit() {
    return defined('PHPUNIT_RUNNER');
  }

  function isResetRequest() {
    return array_key_exists('reset', $_POST);
  }

  function isSubmitRequest() {
    return array_key_exists('submit', $_POST);
  }

}
