<?php

namespace Arrow\Ajax;

class JsonPrinterTest extends \WP_UnitTestCase {

  public $printer;

  function setUp() {
    parent::setUp();

    $this->printer = new JsonPrinter();
  }

  function test_it_knows_if_running_inside_php_unit() {
    $this->assertTrue($this->printer->isPHPUnit());
  }

  function test_it_stops_script_execution_on_quit() {
    $this->printer->quit();
    $this->assertTrue($this->printer->didQuit);
  }

  function test_it_can_set_response_status_code() {
    $this->printer->statusHeader(200);
    $this->assertEquals(200, $this->printer->status);
  }

  function test_it_can_autoset_response_status_code() {
    $this->printer->header('Status', 302);
    $this->assertEquals(302, $this->printer->status);
  }

  function test_it_can_add_headers() {
    $this->printer->header('Content-Type', 'application/json');
    $this->assertEquals('application/json', $this->printer->didHeaders['Content-Type']);
  }

  function getOutput() {
    $output = ob_get_clean();
    return json_decode($output, true);
  }

  function test_it_can_send_content_type_header_with_json_response() {
    $data = array(
      'one' => 1
    );

    $this->printer->send($data);
    $output = $this->getOutput();
    $this->assertEquals('application/json', $this->printer->didHeaders['Content-Type']);
  }

  function test_it_quits_after_sending_json() {
    $this->printer->send(array('one' => 1));
    $output = $this->getOutput();

    $this->assertTrue($this->printer->didQuit);
  }

  function test_it_can_send_error_as_json() {
    $error = array(
      'foo' => 'bar'
    );

    $this->printer->sendError($error, 405);
    $output = $this->getOutput();

    $this->assertEquals(405, $this->printer->didHeaders['Status']);
    $this->assertEquals(array('foo' => 'bar'), $output['data']['error']);
    $this->assertTrue($this->printer->didQuit);
  }

  function test_it_can_send_data_as_json() {
    $data = array(
      'lorem' => 'ipsum'
    );

    $this->printer->sendSuccess($data);
    $output = $this->getOutput();

    $this->assertEquals(200, $this->printer->status);
    $this->assertEquals(array('lorem' => 'ipsum'), $output['data']);
    $this->assertTrue($this->printer->didQuit);
  }

}
