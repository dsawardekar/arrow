<?php

namespace Arrow\Asset\Manifest;

use Encase\Container;

class DirScannerTest extends \PHPUnit_Framework_TestCase {

  public $container;
  public $scanner;
  public $collector;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container
      ->singleton('manifestRanker', 'Arrow\Asset\Manifest\Ranker')
      ->singleton('manifestFileCollector', 'Arrow\Asset\Manifest\FileCollector')
      ->factory('manifestDirScanner', 'Arrow\Asset\Manifest\DirScanner');

    $this->scanner   = $this->container->lookup('manifestDirScanner');
    $this->collector = $this->container->lookup('manifestFileCollector');
  }

  function test_it_can_create_new_dir_scanner() {
    $actual = $this->scanner->getScanner();
    $this->assertInstanceOf('Arrow\Asset\Manifest\DirScanner', $actual);
  }

  function test_it_has_a_container() {
    $this->assertSame($this->container, $this->scanner->container);
  }

  function test_it_has_a_manifest_file_collector() {
    $this->assertSame($this->collector, $this->scanner->manifestFileCollector);
  }

  function test_it_can_glob_for_files_of_extension() {
    $files = $this->scanner->globForFiles('test/manifest/shallow', 'js');
    $this->assertContains('test/manifest/shallow/a.js', $files);
    $this->assertContains('test/manifest/shallow/b.js', $files);
    $this->assertContains('test/manifest/shallow/c.js', $files);
  }

  function test_it_can_glob_for_dirs() {
    $dirs = $this->scanner->globForDirs('test/manifest/models');
    $this->assertEquals(array('test/manifest/models/admin'), $dirs);
  }

  function test_it_can_collect_files_in_dir() {
    $this->scanner->collectFiles('test/manifest/shallow', 'js');
    $files = $this->collector->getFiles();

    $this->assertContains('test/manifest/shallow/a.js', $files);
    $this->assertContains('test/manifest/shallow/b.js', $files);
    $this->assertContains('test/manifest/shallow/c.js', $files);
  }

  function test_it_can_scan_and_collect_files_in_dir() {
    $this->scanner->scan('test/manifest/shallow', 'js');
    $files = $this->collector->getFiles();

    $this->assertContains('test/manifest/shallow/a.js', $files);
    $this->assertContains('test/manifest/shallow/b.js', $files);
    $this->assertContains('test/manifest/shallow/c.js', $files);
  }

  function test_it_can_scan_and_collect_files_recursively() {
    $this->scanner->scan('test/manifest/models', 'js');
    $files = $this->collector->getFiles();

    $this->assertContains('test/manifest/models/a.js', $files);
    $this->assertContains('test/manifest/models/b.js', $files);
    $this->assertContains('test/manifest/models/admin/a.js', $files);
    $this->assertContains('test/manifest/models/admin/b.js', $files);
    $this->assertContains('test/manifest/models/admin/c.js', $files);
  }

  function test_it_can_scan_and_collect_app_files() {
    $this->scanner->scan('test/manifest/app', 'js');
    $files = $this->collector->getFiles();

    $expected = array(
      'test/manifest/app/initializers/a.js',
      'test/manifest/app/initializers/b.js',
      'test/manifest/app/models/a.js',
      'test/manifest/app/models/b.js',
      'test/manifest/app/routes/b.js',
      'test/manifest/app/routes/a.js',
      'test/manifest/app/controllers/a.js',
      'test/manifest/app/controllers/b.js',
    );

    $this->assertEquals($expected, $files);
  }

}
