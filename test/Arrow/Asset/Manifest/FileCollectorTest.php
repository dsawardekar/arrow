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
      ->singleton('manifestDirScanner', 'Arrow\Asset\Manifest\DirScanner')
      ->singleton('manifestFileCollector', 'Arrow\Asset\Manifest\FileCollector');

    $this->collector = $this->container->lookup('manifestFileCollector');
    $this->scanner   = $this->container->lookup('manifestDirScanner');
  }

  function test_it_can_be_reset() {
    $this->collector->reset();
    $this->assertEmpty($this->collector->getFiles());
  }

  function test_it_can_build_path_to_loader_file() {
    $actual = $this->collector->loaderPathFor('foo');
    $this->assertEquals('foo/.loader', $actual);
  }

  function test_it_knows_if_loader_file_is_absent() {
    $actual = $this->collector->hasLoader('foo');
    $this->assertFalse($actual);
  }

  function test_it_knows_if_loader_file_is_present() {
    $actual = $this->collector->hasLoader('test/manifest/inorder');
    $this->assertTrue($actual);
  }

  function test_it_has_empty_load_order_if_loader_is_absent() {
    $actual = $this->collector->loadOrderFor('test/manifest/models');
    $this->assertEmpty($actual);
  }

  function test_it_has_correct_load_order_if_loader_is_present() {
    $actual = $this->collector->loadOrderFor('test/manifest/inorder');
    $expected = array('dolor', 'ipsum', 'lorem');
    $this->assertEquals($expected, $actual);
  }

  /* integration tests */
  function test_it_can_sort_files_without_load_order() {
    $files  = glob('test/manifest/models/*.js');
    $dir    = 'test/manifest/models';
    $actual = $this->collector->sortFiles($dir, $files);
    $expected = array(
      'test/manifest/models/a.js',
      'test/manifest/models/b.js'
    );

    $this->assertEquals($expected, $actual);
  }

  function test_it_can_rank_files_with_load_order() {
    $files  = glob('test/manifest/inorder/*.js');
    $dir    = 'test/manifest/inorder';
    $actual = $this->collector->sortFiles($dir, $files);
    $expected = array(
      'test/manifest/inorder/dolor.js',
      'test/manifest/inorder/ipsum.js',
      'test/manifest/inorder/lorem.js'
    );

    $this->assertEquals($expected, $actual);
  }

  function test_it_can_collect_files_in_dir() {
    $this->scanner->scan('test/manifest/models', 'js', true);
    $actual = $this->collector->getFiles();
    $expected = array(
      'test/manifest/models/a.js',
      'test/manifest/models/b.js',
      'test/manifest/models/admin/a.js',
      'test/manifest/models/admin/b.js',
      'test/manifest/models/admin/c.js'
    );

    $this->assertEquals($expected, $actual);
  }

  function test_it_can_collect_files_in_dir_in_custom_order() {
    $this->scanner->scan('test/manifest/inorder', 'js', true);
    $actual = $this->collector->getFiles();
    $expected = array(
      'test/manifest/inorder/dolor.js',
      'test/manifest/inorder/ipsum.js',
      'test/manifest/inorder/lorem.js',
      'test/manifest/inorder/public/a.js',
      'test/manifest/inorder/public/b.js',
    );

    $this->assertEquals($expected, $actual);
  }

}
