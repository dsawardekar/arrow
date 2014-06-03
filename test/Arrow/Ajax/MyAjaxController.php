<?php

namespace Arrow\Ajax;

class MyAjaxController extends Controller {

  function publicActions() {
    return array('create', 'update', 'delete');
  }

  function index() {
    $this->sendSuccess('index');
  }

  function create() {
    $this->sendSuccess('create');
  }

  function update() {
    $this->sendSuccess('update');
  }

  function show() {
    $this->sendSuccess('show');
  }

  function delete() {
    $this->sendSuccess('delete');
  }

  function hello() {
    $this->sendSuccess(
      array('message' => 'Hello ' . $this->params['name'])
    );
  }
}

