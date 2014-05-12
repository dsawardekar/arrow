<?php

class ArrowPluginLoader {

  static $instance = null;
  static function getInstance() {
    if (is_null(self::$instance)) {
      self::$instance = new ArrowPluginLoader();
    }

    return self::$instance;
  }

  public $plugins = array();

  function __construct() {
    add_action('plugins_loaded', array($this, 'load'));
  }

  function isRegistered($name) {
    return array_key_exists($name, $this->plugins);
  }

  function register($name, $arrowVersion, $callback) {
    if ($this->isRegistered($name)) {
      return;
    }

    $plugin = array(
      'name' => $name,
      'arrowVersion' => $arrowVersion,
      'callback' => $callback
    );

    $this->plugins[$name] = $plugin;
  }

  function load() {
    $sorted = $this->sortPlugins();

    foreach ($sorted as $plugin) {
      $this->loadPlugin($plugin);
    }
  }

  function loadPlugin(&$plugin) {
    $callback = $plugin['callback'];
    $name     = $plugin['name'];

    if (!is_null($callback)) {
      call_user_func($callback, $name);
    }

    $this->sendPluginEvent($name, 'loaded');
    $this->sendPluginEvent($name, 'ready');
  }

  function sendPluginEvent($name, $eventType) {
    $action = 'arrow-plugin-' . $name . "-$eventType";
    do_action($action, $name);
  }

  function sortPlugins() {
    $plugins = array_values($this->plugins);
    usort($plugins, array($this, 'comparePlugins'));

    return $plugins;
  }

  /* order is flipped because we want descending order */
  function comparePlugins(&$a, &$b) {
    $versionA = $a['arrowVersion'];
    $versionB = $b['arrowVersion'];

    if (version_compare($versionA, $versionB, '<')) {
      return 1;
    } elseif (version_compare($versionA, $versionB, '>')) {
      return -1;
    } else {
      return 0;
    }
  }

}
