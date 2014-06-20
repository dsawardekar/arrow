<?php

namespace Arrow\Asset\Manifest;

use Encase\Container;

class AppManifestTest extends \WP_UnitTestCase {

  public $container;
  public $manifest;
  public $pluginMeta;

  function setUp() {
    parent::setUp();

    $this->container = new Container();
    $this->container
      ->object('pluginMeta', new \Arrow\PluginMeta('test/plugins/sample/sample.php'))
      ->packager('assetPackager', 'Arrow\Asset\Packager')
      ->singleton('manifestRanker', 'Arrow\Asset\Manifest\Ranker')
      ->singleton('manifestDirScanner', 'Arrow\Asset\Manifest\DirScanner')
      ->singleton('manifestFileCollector', 'Arrow\Asset\Manifest\FileCollector')
      ->singleton('appManifest', 'Arrow\Asset\Manifest\AppManifest');

    $this->pluginMeta = $this->container->lookup('pluginMeta');
    $this->manifest   = $this->container->lookup('appManifest');
    $this->scanner    = $this->container->lookup('manifestDirScanner');
  }

  function disableDebug() {
    $this->pluginMeta = \Mockery::mock('Arrow\PluginMeta[getDebug]', array('test/plugins/sample/sample.php'));
    $this->pluginMeta->shouldReceive('getDebug')->withNoArgs()->andReturn(false);

    $this->container->object('pluginMeta', $this->pluginMeta);
    $this->container->singleton('appManifest', 'Arrow\Asset\Manifest\AppManifest');

    $this->manifest = $this->container->lookup('appManifest');
  }

  function test_it_has_plugin_meta() {
    $this->assertSame($this->pluginMeta, $this->manifest->pluginMeta);
  }

  function test_it_has_a_dir_scanner() {
    $this->assertSame($this->scanner, $this->manifest->manifestDirScanner);
  }

  function test_it_has_path_to_app_dir() {
    $this->pluginMeta->file = 'test/plugins/sample/sample.php';
    $actual = $this->manifest->getAppDir();
    $this->assertEquals('test/plugins/sample/js/app', $actual);
  }

  function test_it_knows_if_app_dir_is_absent() {
    $this->pluginMeta->file = 'test/plugins/foo/foo.php';
    $this->assertFalse($this->manifest->hasAppDir());
  }

  function test_it_knows_if_app_dir_is_present() {
    $this->pluginMeta->file = 'test/plugins/sample/sample.php';
    $actual = $this->manifest->getAppDir();

    $this->assertTrue($this->manifest->hasAppDir());
  }

  function test_it_gets_debug_mode_from_plugin_meta() {
    $this->pluginMeta = \Mockery::mock('Arrow\PluginMeta[getDebug]', array('foo.php'));
    $this->pluginMeta->shouldReceive('getDebug')->withNoArgs()->andReturn('foo');

    $this->container->object('pluginMeta', $this->pluginMeta);
    $this->container->singleton('appManifest', 'Arrow\Asset\Manifest\AppManifest');

    $this->manifest = $this->container->lookup('appManifest');
    $this->assertEquals('foo', $this->manifest->getDebug());
  }

  function test_it_has_app_name() {
    $this->assertEquals('sample', $this->manifest->getAppName());
  }

  function test_it_knows_if_it_cannot_scan_app_dir_in_production_mode() {
    $this->disableDebug();
    $this->assertFalse($this->manifest->canScan());
  }

  function test_it_knows_if_it_cannot_scan_app_dir_if_absent() {
    $this->pluginMeta->file = 'test/plugins/foo/foo.php';
    $this->assertFalse($this->manifest->canScan());
  }

  function test_it_knows_if_it_can_scan_dir_if_present() {
    $this->pluginMeta->file = 'test/plugins/sample/sample.php';
    $this->assertTrue($this->manifest->canScan());
  }

  function test_it_knows_scripts_output_name() {
    $this->assertEquals('sample-app', $this->manifest->getScriptsOutput());
  }

  function test_it_knows_styles_output_name() {
    $this->assertEquals('sample-app', $this->manifest->getStylesOutput());
  }

  function test_it_knows_templates_output_name() {
    $this->assertEquals('sample-app', $this->manifest->getTemplatesOutput());
  }

  function test_it_can_convert_path_to_slug() {
    $this->pluginMeta->file = 'test/plugins/sample/sample.php';
    $actual = $this->manifest->toSlug('test/plugins/sample/js/app/models/a.js');
    $this->assertEquals('app/models/a', $actual);
  }

  function test_it_can_find_app_scripts() {
    $this->pluginMeta->file = 'test/plugins/sample/sample.php';
    $scripts = $this->manifest->getScripts();

    $expected = array(
      'app/initializers/a',
      'app/initializers/b',
      'app/models/a',
      'app/models/b',
      'app/routes/b',
      'app/routes/a',
      'app/controllers/a',
      'app/controllers/b'
    );

    $this->assertEquals($expected, $scripts);
  }

  function test_it_can_find_app_styles() {
    $this->pluginMeta->file = 'test/plugins/sample/sample.php';
    $styles = $this->manifest->getStyles();

    $expected = array(
      'app/styles/b',
      'app/styles/c',
      'app/styles/a'
    );

    $this->assertEquals($expected, $styles);
  }

  function test_it_can_find_app_templates() {
    $this->pluginMeta->file = 'test/plugins/sample/sample.php';
    $templates = $this->manifest->getTemplates();

    $expected = array(
      'test/plugins/sample/js/app/templates/application.hbs',
      'test/plugins/sample/js/app/templates/comments.hbs',
      'test/plugins/sample/js/app/templates/posts.hbs',
      'test/plugins/sample/js/app/templates/posts/_partial.hbs',
    );

    $this->assertEquals($expected, $templates);
  }

  function test_it_can_build_template_slug() {
    $this->pluginMeta->file = 'test/plugins/sample/sample.php';
    $path = 'test/plugins/sample/js/app/templates/application.hbs';
    $actual = $this->manifest->toTemplateSlug($path);

    $this->assertEquals('application', $actual);
  }

  function test_it_can_build_child_template_slug() {
    $this->pluginMeta->file = 'test/plugins/sample/sample.php';
    $path = 'test/plugins/sample/js/app/templates/posts/_partial.hbs';
    $actual = $this->manifest->toTemplateSlug($path);

    $this->assertEquals('posts/_partial', $actual);
  }

  function test_it_can_include_template() {
    $output = $this->pluginMeta->file = 'test/plugins/sample/sample.php';
    $template = 'test/plugins/sample/js/app/templates/application.hbs';
    ob_start();
    $this->manifest->includeTemplate($template);
    $html = ob_get_clean();

    $this->assertContains("data-template-name='application'", $html);
    $this->assertContains('<h1>application</h1>', $html);
  }

  function test_it_can_include_templates() {
    $this->pluginMeta->file = 'test/plugins/sample/sample.php';
    ob_start();
    $this->manifest->loadTemplates();
    $html = ob_get_clean();

    $this->assertContains("data-template-name='application'", $html);
    $this->assertContains("data-template-name='posts'", $html);
    $this->assertContains("data-template-name='comments'", $html);
    $this->assertContains("data-template-name='posts/_partial'", $html);
  }

  function test_it_works_in_debug_mode() {
    ob_start();
    $this->manifest->load();
    $html = ob_get_clean();

    $scriptLoader = $this->container->lookup('adminScriptLoader');

    $this->assertTrue($scriptLoader->isScheduled('app/initializers/a'));
    $this->assertTrue($scriptLoader->isScheduled('app/initializers/b'));
    $this->assertTrue($scriptLoader->isScheduled('app/models/a'));
    $this->assertTrue($scriptLoader->isScheduled('app/models/b'));
    $this->assertTrue($scriptLoader->isScheduled('app/routes/b'));
    $this->assertTrue($scriptLoader->isScheduled('app/routes/a'));
    $this->assertTrue($scriptLoader->isScheduled('app/controllers/a'));
    $this->assertTrue($scriptLoader->isScheduled('app/controllers/b'));

    $stylesheetLoader = $this->container->lookup('adminStylesheetLoader');

    $this->assertTrue($stylesheetLoader->isScheduled('app/styles/a'));
    $this->assertTrue($stylesheetLoader->isScheduled('app/styles/b'));
    $this->assertTrue($stylesheetLoader->isScheduled('app/styles/c'));

    $this->assertContains("data-template-name='application'", $html);
    $this->assertContains("data-template-name='posts'", $html);
    $this->assertContains("data-template-name='comments'", $html);
    $this->assertContains("data-template-name='posts/_partial'", $html);
  }

  function test_it_works_in_production_mode() {
    $this->disableDebug();

    ob_start();
    $this->manifest->load();
    $html = ob_get_clean();

    $scriptLoader = $this->container->lookup('adminScriptLoader');
    $stylesheetLoader = $this->container->lookup('adminStylesheetLoader');

    $this->assertTrue($scriptLoader->isScheduled('sample-app'));
    $this->assertTrue($stylesheetLoader->isScheduled('sample-app'));
    $this->assertContains('sample-app-templates', $html);
  }

}
