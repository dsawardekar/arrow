<?php

namespace Arrow\Asset\Manifest;

use Encase\Container;

class FileCollectorTest extends \PHPUnit_Framework_TestCase {

  public $container;
  public $collector;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container
      ->singleton('manifestFileCollector', 'Arrow\Asset\Manifest\FileCollector');

    $this->collector = $this->container->lookup('manifestFileCollector');
  }

  function test_it_can_be_reset() {
    $this->collector->reset();
    $this->assertEmpty($this->collector->getFiles());
  }

  function test_it_can_store_files() {
    $this->collector->files = array('foo');
    $this->assertEquals(array('foo'), $this->collector->getFiles());
  }

  function test_it_can_collect_single_item() {
    $this->collector->collect('foo', 'bar');
    $this->assertEquals(array('bar'), $this->collector->getFiles());
  }

  function test_it_can_collect_multiple_items() {
    $this->collector->collect('foo', array('one', 'two', 'three'));
    $this->assertEquals(array('one', 'two', 'three'), $this->collector->getFiles());
  }

}
