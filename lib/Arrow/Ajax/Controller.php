<?php

namespace Arrow\Ajax;

class Controller {

  public $container;
  public $ajaxJsonPrinter;
  public $params;

  function needs() {
    return array('ajaxJsonPrinter');
  }

  function publicActions() {
    return array();
  }

  function adminActions() {
    return array(
      'index', 'create', 'update', 'show', 'delete'
    );
  }

  /* not strictly REST to allow for older PHP */
  function actionMethods() {
    return array(
      'index'  => array('GET'),
      'create' => array('POST'),
      'update' => array('POST', 'PUT', 'PATCH'),
      'show'   => array('GET'),
      'delete' => array('GET', 'DELETE')
    );
  }

  function capability() {
    return 'manage_options';
  }

  function sendSuccess($data, $statusCode = 200) {
    return $this->ajaxJsonPrinter->sendSuccess($data, $statusCode);
  }

  function sendError($error, $statusCode = 403) {
    return $this->ajaxJsonPrinter->sendError($error, $statusCode);
  }

  function process($action, $params = array()) {
    if (method_exists($this, $action)) {
      $this->params = $params;
      $this->$action();
    } else {
      $this->sendError('invalid_action');
    }
  }

  /* abstract */
  function index() {

  }

  function create() {

  }

  function update() {

  }

  function show() {

  }

  function delete() {

  }

}
