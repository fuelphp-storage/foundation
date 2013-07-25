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
use Fuel\Foundation\Facades\Composer;
use Fuel\Foundation\Facades\Dependency;
use Fuel\Foundation\Facades\Error;
use Fuel\Foundation\Facades\Package;

/**
 * Get the Composer autoloader instance and allow the framework to use it
 */
Composer::setLoader(self::$loader);

/**
 * Setup the shutdown, error & exception handlers
 */
Error::setHandler();

/**
 * Setup the Dependency Container
 */
Dependency::setDic();

/**
 * Setup the Alias Manager
 */
Alias::setManager();

/**
 * Alias all Facades to global
 */
Alias::aliasNamespace('Fuel\Foundation\Facades', '');

/**
 * Run the composer package bootstraps
 */
Package::bootstrap();

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
 * And finish by running the global applications bootstrap, if present
 */
if (file_exists(APPSPATH.'bootstrap.php'))
{
	include APPSPATH.'bootstrap.php';
}
