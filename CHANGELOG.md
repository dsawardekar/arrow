# Changelog

### 0.6.1

* Fixes bug in ArrowPluginLoader Static API.

### 0.6.0

* Major revisions to the ArrowPluginLoader.
* Switched to a declarative API, you declare the plugin class
  and the autoloader will take care of creating it at the right time
  in the right order.

### 0.5.1

* Fixes issues with travis.

### 0.5.0

* Adds automatic vendor autoloading.

### 0.4.2

* Adds 'safeText' custom Valitron rule.

### 0.4.1

* Makes slug unique when using theme-custom.

### 0.4.0

* Reverses plugin order to match Composer's default 'prepend-autoloader'
  behaviour.

### 0.3.1

* Adds helpers to check for custom stylesheets.
* Adds pluginMeta need to OptionsValidator.

### 0.3.0

* Adds ifdef to prevent class redeclaration.

### 0.2.0

* Adds PluginMeta object.

### 0.1.0

* Initial Version.
