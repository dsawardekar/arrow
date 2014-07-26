# Changelog

### Current

* Fixes development mode CSS path bug in OptionsManifest.
* Adds extra alias in development to allow frontend build scripts to
  build app.js or wp-foo.js.

### 1.4.0

* Makes the variable that the plugin writes configurable.
  * The options variable now will correspond to the underscorized plugin
    slug name.
* Adds support for localized strings.
  * The options object is now nested inside the main plugin variable.
  * Type information is now preserved from PHP to Javascript.

### 1.3.0

* OptionsManifest now checks if the slugs being added exist before using
  them.
  * Fixed tests affected by this change.
* Fixes script localization if only single script is to be loaded.

### 1.2.2

* Adds support for streaming assets.
* Makes the localization of the last script in place to support
  streaming mode.

### 1.2.1

* Composer version fix.

### 1.2.0

* Adds Manifests.
  * Manifests describe, scripts, styles and optionally templates for a
    page.
* Makes the Ajax API more Restful.
* Various bugfixes.

### 1.1.0

* Adds auto initialization of custom rules for Validator used with Ajax.
* Fixes bug in OptionsStore, setOption does not clobber other options on
  non-preloaded store.

### 1.0.0

* Major reorganization of APIs.
* Upgrades to Encase and adds Packagers for each feature.
* Refactored Arrow\Twig to use packagers.
* Arrow\Ajax\Packager bundles all ajax functionality except the custom
  controllers.
* Ajax\Options is the new Options API.
  * now only needs Validator rules
  * default empty validator is bundled so rules are optional.
  * Options API is currently based on Ember, but can be modified to use
    any frontend framework by overridding the PluginMeta.

### 0.8.2

* Fixes json_last_error() for PHP 5.5.

### 0.8.1

* Improves Tests.

### 0.8.0

* Adds getDebug to PluginMeta.
* Adds Minification support.
  * If a corresponding .min.js version is present it is used instead.
  * Does not minify in debug mode.
* Changes getVersion to use timestamp for cache busting.
  * Timestamp is used in development only.
* Adds Ajax API
  * Restful API with allowance for older PHP.
  * Uses admin-ajax.php but routes requests to corresponding
    controllers.
  * Controllers can be free form, or follow REST conventions.
* Adds Ember based OptionsManager.

### 0.7.0

* Adds Plugin base class.

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
