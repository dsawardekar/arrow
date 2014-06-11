<?php

namespace Arrow\Ajax;

class MyAjaxController extends Controller {

  function publicActions() {
    return array('create', 'update', 'delete');
  }

  function index() {
    return 'index';
  }

  function create() {
    return 'create';
  }

  function update() {
    return 'update';
  }

  function show() {
    return 'show';
  }

  function delete() {
    return 'delete';
  }

  function helloError() {
    return $this->error('helloError');
  }

  function hello() {
    $this->sendSuccess(
      array('message' => 'Hello ' . $this->params['name'])
    );
  }

  function helloException() {
    throw new \Exception('helloException');
  }
}

