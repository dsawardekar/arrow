<?php

namespace Arrow\OptionsManager;

class OptionsSentry extends \Arrow\Sentry {

  function needs() {
    return array_merge(
      parent::needs(),
      array('pluginMeta')
    );
  }

  /* only allow POST */
  function getValidMethod() {
    return 'POST';
  }

  /* only allow if referer is plugin options page */
  function getValidReferer() {
    return $this->pluginMeta->getOptionsUrl();
  }

  /* only allow logged in users */
  function getValidLoggedIn() {
    return true;
  }

  /* only allow access if user has manage_options permissions */
  function getValidPermissions() {
    return 'manage_options';
  }

  /* nonce is my_plugin_options_wpnonce */
  function getNonceName() {
    $name = $this->pluginMeta->getOptionsKey();
    $name .= '_wpnonce';

    return str_replace('-', '_', $name);
  }

}
