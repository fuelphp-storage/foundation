<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

use Fuel\Foundation\Facades\Alias;
use Fuel\Foundation\Facades\Error;
use Fuel\Foundation\Facades\Package;

/**
 * Some handy constants
 */
define('DS', DIRECTORY_SEPARATOR);
define('CRLF', chr(13).chr(10));

/**
 * Do we have access to mbstring?
 * We need this in order to work with UTF-8 strings
 */
define('MBSTRING', function_exists('mb_get_info'));

/**
 * Create some class aliases to get the bootstrapping to work
 */
class_alias('Fuel\Foundation\Facades\Composer', 'Composer');
class_alias('Fuel\Foundation\Facades\Dependency', 'Dependency');
class_alias('Fuel\Foundation\Facades\Alias', 'Alias');

/**
 * Get the Composer autoloader instance and allow the framework to use it
 */
Composer::initialize(self::$loader);

/**
 * Setup the shutdown, error & exception handlers
 */
Error::initialize();

/**
 * Setup the Dependency Container
 */
Dependency::initialize();

/**
 * Run the composer package bootstraps
 */
Package::initialize();

/**
 * Alias all Facades to global
 */
Dependency::resolve('alias')->aliasNamespace('Fuel\Foundation\Facades', '');

/**
 * Create the global Input instance and import globals
 */
Input::loadGlobals();

/**
 * Create the global Config instance and import global configuration
 */
Config::loadGlobals();

/**
 * Alias all Base controllers to Fuel\Controller
 */
Alias::aliasNamespace('Fuel\Foundation\Controller', 'Fuel\Controller');

/**
 * And get the framework going
 */
Fuel::initialize();
