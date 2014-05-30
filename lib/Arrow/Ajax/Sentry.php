<?php

namespace Arrow\Ajax;

class Sentry extends \Arrow\Sentry {

  /* only allow requests from options page */
  function getValidReferer() {
    return $this->pluginMeta->getOptionsUrl();
  }

  /* only allow logged in users */
  function getValidLoggedIn() {
    return true;
  }

  /* default nonce name */
  function getNonceName() {
    return 'nonce';
  }

}
