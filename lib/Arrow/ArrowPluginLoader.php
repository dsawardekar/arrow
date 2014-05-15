<?php

if (class_exists('ArrowPluginLoader') === false) {

  class ArrowPluginLoader {

    static public $instance = null;
    static public function getInstance() {
      if (is_null(self::$instance)) {
        self::$instance = new ArrowPluginLoader();
      }

      return self::$instance;
    }

    public $plugins = array();
    public $currentPlugin = null;

    public function __construct() {
      add_action('plugins_loaded', array($this, 'load'));
    }

    public function register($file, $arrowVersion, $callback) {
      if ($this->isRegistered($file)) {
        return;
      }

      $plugin = array(
        'file' => $file,
        'arrowVersion' => $arrowVersion,
        'callback' => $callback
      );

      $this->plugins[$file] = $plugin;
    }

    function isRegistered($file) {
      return array_key_exists($file, $this->plugins);
    }

    function load() {
      $sorted = $this->sortPlugins();

      foreach ($sorted as $plugin) {
        $this->requirePlugin($plugin);
      }

      foreach ($sorted as $plugin) {
        $this->loadPlugin($plugin);
      }
    }

    function requirePlugin($plugin) {
      $file = $plugin['file'];
      $path = $this->getPluginAutoloadPath($file);

      $this->requirePluginAutoload($path);
    }

    function getPluginAutoloadPath($file) {
      return plugin_dir_path($file) . 'vendor/autoload.php';
    }

    function requirePluginAutoload($path) {
      if (file_exists($path)) {
        if (!defined('PHPUNIT_RUNNER')) {
          require_once($path);
        } else {
          require($path);
        }
      }
    }

    function loadPlugin(&$plugin) {
      $callback = $plugin['callback'];
      $file     = $plugin['file'];
      $name     = basename($file, '.php');
      $this->currentPlugin = $name;

      if (!is_null($callback)) {
        call_user_func($callback);
      }

      $this->sendPluginEvent($name, 'loaded');
      $this->sendPluginEvent($name, 'ready');
    }

    function sendPluginEvent($name, $eventType) {
      $action = 'arrow-plugin-' . $name . "-$eventType";
      do_action($action);
    }

    function sortPlugins() {
      $plugins = array_values($this->plugins);
      usort($plugins, array($this, 'comparePlugins'));

      return $plugins;
    }

    /* Ascending order, ensures default 'prepend-autoloader' works
     * out of the box */
    function comparePlugins(&$a, &$b) {
      $versionA = $a['arrowVersion'];
      $versionB = $b['arrowVersion'];

      if (version_compare($versionA, $versionB, '<')) {
        return -1;
      } elseif (version_compare($versionA, $versionB, '>')) {
        return 1;
      } else {
        return 0;
      }
    }

  }

}
