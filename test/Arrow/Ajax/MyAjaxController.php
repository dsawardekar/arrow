<?php

namespace Arrow\Ajax;

class MyAjaxController extends Controller {

  function all() {
    return 'all';
  }

  function get() {
    return 'get';
  }

  function post() {
    return 'post';
  }

  function put() {
    return 'put';
  }

  function patch() {
    return 'patch';
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

  function doTrue() {
    return true;
  }

  function doFalse() {
    return false;
  }
}

