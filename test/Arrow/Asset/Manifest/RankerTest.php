<?php

namespace Arrow\Asset\Manifest;

use Encase\Container;

class RankerTest extends \PHPUnit_Framework_TestCase {

  public $container;
  public $ranker;

  function setUp() {
    $this->container = new Container();
    $this->container
      ->singleton('ranker', 'Arrow\Asset\Manifest\Ranker');

    $this->ranker = $this->container->lookup('ranker');
  }

  function test_it_can_build_path_to_loader_file() {
    $actual = $this->ranker->loaderPathFor('foo');
    $this->assertEquals('foo/.loader', $actual);
  }

  function test_it_knows_if_loader_file_is_absent() {
    $actual = $this->ranker->hasLoader('foo');
    $this->assertFalse($actual);
  }

  function test_it_knows_if_loader_file_is_present() {
    $actual = $this->ranker->hasLoader('test/manifest/inorder');
    $this->assertTrue($actual);
  }

  function test_it_has_empty_load_order_if_loader_is_absent() {
    $actual = $this->ranker->loadOrderFor('test/manifest/models');
    $this->assertEmpty($actual);
  }

  function test_it_has_correct_load_order_if_loader_is_present() {
    $actual = $this->ranker->loadOrderFor('test/manifest/inorder');
    $expected = array('dolor', 'ipsum', 'lorem');
    $this->assertEquals($expected, $actual);
  }

  function test_it_can_rank_files_without_load_order() {
    $files  = glob('test/manifest/models/*.js');
    $dir    = 'test/manifest/models';
    $actual = $this->ranker->rank($dir, $files);
    $expected = array(
      'test/manifest/models/a.js',
      'test/manifest/models/b.js'
    );

    $this->assertEquals($expected, $actual);
  }

  function test_it_can_rank_files_with_load_order() {
    $files  = glob('test/manifest/inorder/*.js');
    $dir    = 'test/manifest/inorder';
    $actual = $this->ranker->rank($dir, $files);
    $expected = array(
      'test/manifest/inorder/dolor.js',
      'test/manifest/inorder/ipsum.js',
      'test/manifest/inorder/lorem.js'
    );

    $this->assertEquals($expected, $actual);
  }

  function test_it_can_rank_dirs_in_correct_order() {
    $dirs  = glob('test/manifest/app/*');
    $dir    = 'test/manifest/app';
    $actual = $this->ranker->rank($dir, $dirs);
    $expected = array(
      'test/manifest/app/initializers',
      'test/manifest/app/models',
      'test/manifest/app/routes',
      'test/manifest/app/controllers'
    );

    $this->assertEquals($expected, $actual);
  }

}
