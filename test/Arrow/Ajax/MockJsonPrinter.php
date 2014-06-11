<?php

namespace Arrow\Ajax;

class MockJsonPrinter {

  public $data       = null;
  public $statusCode = null;

  function sendSuccess($data, $statusCode = '200 OK') {
    $this->data = $data;
    $this->statusCode = $statusCode;
  }

  function sendError($error, $statusCode = '403') {
    $this->data = $error;
    $this->statusCode = $statusCode;
  }

}

