<?php

namespace Arrow\Ajax;

class JsonPrinter {

  public $didQuit    = false;
  public $didHeaders = array();
  public $status     = null;

  function send($data) {
    $this->header('Content-Type', 'application/json');

    $started = ob_start('ob_gzhandler');
    if (!$started) {
      ob_start();
    }

    echo json_encode($data);
    $this->quit();
  }

  function sendSuccess($data, $statusCode = '200 OK') {
    $response = array(
      'status' => $statusCode,
      'success' => true,
      'data' => $data
    );

    $this->header('Status', $statusCode);
    $this->send($response);
  }

  function sendError($error, $statusCode = '403 Forbidden') {
    $response = array(
      'status'  => $statusCode,
      'success' => false,
      'data'    => array(
        'error' => $error
      )
    );

    $this->header('Status', $statusCode);
    $this->send($response);
  }

  function header($name, $value) {
    if (!$this->isPHPUnit()) {
      if ($name === 'Status') {
        $this->statusHeader($value);
      } else {
        header("$name: $value");
      }
    } else {
      if ($name === 'Status') {
        $this->status = $value;
      }
      $this->didHeaders[$name] = $value;
    }
  }

  function statusHeader($value) {
    $this->status = $value;

    if (function_exists('http_response_code')) {
      http_response_code($value);
    } else {
      // TODO: write status headers for older PHP
    }
  }

  function quit() {
    $this->didQuit = true;
    if (!$this->isPHPUnit()) {
      die();
    }
  }

  function isPHPUnit() {
    return defined('PHPUNIT_RUNNER');
  }

}
