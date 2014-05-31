<?php

namespace Arrow\Ajax;

class Router {

  public $container;
  public $pluginMeta;
  public $allowPublic = false;

  function needs() {
    return array('pluginMeta', 'ajaxSentry');
  }

  function hookName($public = false) {
    $hook = 'wp_ajax_';
    if ($public) {
      $hook .= 'no_priv_';
    }

    $hook .= $this->pluginMeta->getSlug();
    return str_replace('-', '_', $hook);
  }

  function register($allowPublic = false) {
    $this->allowPublic = $allowPublic;

    if ($allowPublic) {
      add_action($this->hookName(true), array($this, 'processPublic'));
    }

    add_action($this->hookName(), array($this, 'process'));
  }

  function process() {
    if (!$this->authorize()) {
      return false;
    }

    return $this->doProcess();
  }

  function processPublic() {
    if (!$this->authorize(true)) {
      return false;
    }

    return $this->doProcess();
  }

  function doProcess() {
    $name       = $this->ajaxSentry->getController() . 'Controller';
    $controller = $this->container->lookup($name);
    $action     = $this->ajaxSentry->getAction();
    $params     = $this->ajaxSentry->getParams();

    return $controller->process($action, $params);
  }

  function authorize($public = false) {
    if ($public) {
      return $this->ajaxSentry->authorizePublic();
    } else {
      return $this->ajaxSentry->authorize();
    }
  }


}
