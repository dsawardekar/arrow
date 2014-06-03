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
    $loader->schedule(
      'ember', array('dependencies' => array(
        'jquery', 'handlebars')
      )
    );

    $this->loadSupportScripts();

    $loader->schedule(
      $this->getEmberAppSlug(),
      array(
        'dependencies' => array('ember'),
        'localizer' => array($this, 'getEmberConfig')
      )
    );

    $loader->load();
  }

  function loadSupportScripts() {
    $loader = $this->lookup('adminScriptLoader');
    $options = array(
      'dependencies' => array('ember'),
      'version' => $this->lookup('pluginMeta')->getVersion()
    );

    foreach ($this->getSupportScripts() as $script) {
      $loader->schedule($script, $options);
    }
  }

  function getSupportScripts() {
    return array(
      'ember-validations',
      'ember-easyForm'
    );
  }

  function loadStyles() {
    $options = array(
      'in_footer' => true,
      'version' => $this->lookup('pluginMeta')->getVersion()
    );

    $loader = $this->lookup('adminStylesheetLoader');
    $loader->schedule($this->getEmberAppSlug(), $options);

    $this->loadSupportStyles();

    $loader->load();
  }

  function loadSupportStyles() {
    $loader = $this->lookup('adminStylesheetLoader');

    foreach ($this->getSupportStyles() as $style) {
      $loader->schedule($style);
    }
  }

  function getSupportStyles() {
    return array();
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
    $action = str_replace('-', '_', $this->lookup('pluginMeta')->getSlug());
    $url = admin_url('admin-ajax.php');
    $url .= '?action=' . $action;
    $url .= '&admin=1';

    return $url;
  }

  function getNonceValue() {
    return wp_create_nonce($this->lookup('pluginMeta')->getSlug());
  }

}
