<?php

namespace Arrow\Ajax;

class MockJsonPrinter {

  public $data;
  public $statusCode;

  function sendSuccess($data, $statusCode = '200 OK') {
    $this->data = $data;
    $this->statusCode = $statusCode;
  }

  function sendError($error, $statusCode = '403') {
    $this->data = $error;
    $this->statusCode = $statusCode;
  }

}

