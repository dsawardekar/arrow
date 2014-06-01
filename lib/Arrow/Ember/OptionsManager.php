<?php

namespace Arrow\Ember;

use Arrow\OptionsManager\CustomValitronRules;

class OptionsManager {

  public $container;

  function __construct($container) {
    $this->container = $container;
    $container
      ->singleton('optionsStore', 'Arrow\OptionsManager\OptionsStore')
      ->singleton('ajaxSentry', 'Arrow\Ajax\Sentry')
      ->singleton('ajaxJsonPrinter', 'Arrow\Ajax\JsonPrinter')
      ->singleton('ajaxRouter', 'Arrow\Ajax\Router')
      ->singleton('optionsPage', 'Arrow\Ember\OptionsPage')
      ->singleton('optionsValidator', 'Arrow\OptionsManager\OptionsValidator')
      ->singleton('optionsController', 'Arrow\Ember\OptionsController');

    if (!$container->contains('assetManager')) {
      $container->object(
        'assetManager', new \Arrow\AssetManager\AssetManager($container)
      );
    }

    CustomValitronRules::load();

    $this->enable();
  }

  function enable() {
    add_action('admin_menu', array($this, 'onAdminMenu'));
    add_action('admin_init', array($this, 'onAdminInit'));
  }

  function lookup($key) {
    return $this->container->lookup($key);
  }

  function onAdminMenu() {
    $this->lookup('optionsPage')->register();
    $this->loadEmber();
    $this->loadStyles();
  }

  function onAdminInit() {
    $this->lookup('ajaxRouter')->register(
      $this->getAllowPublic()
    );
  }

  /* API endpoint can only be used by admins by default */
  function getAllowPublic() {
    return false;
  }

  function loadEmber() {
    $loader = $this->lookup('adminScriptLoader');
    $loader->schedule('handlebars');
    $loader->schedule('parsley');
    $loader->schedule(
      'ember', array('dependencies' => array(
        'jquery', 'handlebars')
      )
    );

    $loader->schedule(
      $this->getEmberAppSlug(),
      array(
        'dependencies' => array('ember', 'parsley'),
        'localizer' => array($this, 'getEmberConfig')
      )
    );

    $loader->load();
  }

  function loadStyles() {
    $loader = $this->lookup('adminStylesheetLoader');
    $loader->schedule('parsley');
    $loader->schedule($this->getEmberAppSlug());

    $loader->load();
  }

  function getEmberAppSlug() {
    return $this->lookup('pluginMeta')->getSlug() . '-app';
  }

  function getEmberConfig($script) {
    return array(
      'apiEndpoint' => $this->getApiEndpoint(),
      'nonce' => $this->getNonceValue()
    );
  }

  function getApiEndpoint() {
    $url = admin_url('admin-ajax.php');
    $url .= '?action=' . $this->lookup('pluginMeta')->getSlug();
    $url .= '&admin=1';

    return $url;
  }

  function getNonceValue() {
    return wp_create_nonce($this->lookup('pluginMeta')->getSlug());
  }

}
