<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

use Fuel\Foundation\Error;
use Fuel\Foundation\Input;
use Fuel\Foundation\PackageProvider;

use Fuel\Common\DataContainer;

use Fuel\Foundation\Facades\Dependency;

/**
 * Some handy constants
 *
 * @since 1.0.0
 */
define('DS', DIRECTORY_SEPARATOR);
define('CRLF', chr(13).chr(10));

/**
 * Do we have access to mbstring?
 * We need this in order to work with UTF-8 strings
 *
 * @since 1.0.0
 */
define('MBSTRING', function_exists('mb_get_info'));

/**
* Insane workaround for https://bugs.php.net/bug.php?id=64761
*/
function InputClosureBindStupidWorkaround($event, $input, $autoloader)
{
	// setup a shutdown event for writing cookies
	$event->on('shutdown', function($event) { $this->getCookie()->send(); }, $input);

	// and one to write the classmap cache
	// TODO: better way to determine the location of the class cache
	$event->on('shutdown', function($event) {
		$classMap = array_filter($this->getClassMap());
		if (isset($classMap['FuelPHPexpirationTimestamp']))
		{
			if ($classMap['FuelPHPexpirationTimestamp'] < time())
			{
				unlink(APPSPATH.'demo/cache/class_cache.php');
				return;
			}
		}
		else
		{
			// have it expire at least once a day
			$classMap['FuelPHPexpirationTimestamp'] = time() + (defined('CLASS_CACHE_EXPIRE') ? CLASS_CACHE_EXPIRE : 86400);
		}
		file_put_contents(APPSPATH.'demo/cache/class_cache.php', '<?php'."\n\n".'return '.var_export($classMap, true).';');
	}, $autoloader);
}

/**
 * Framework bootstrap, encapsulated to keep the global scope clean and prevent
 * interference with Composer, as this runs in the scope of the autoloader
 */
$bootstrapFuel = function()
{
	/**
	 * Load the cached class map if present
	 */
	if (file_exists(APPSPATH.'demo/cache/class_cache.php'))
	{
		self::$loader->addClassMap(include  APPSPATH.'demo/cache/class_cache.php');
	}

	/**
	 * Setup the shutdown, error & exception handlers
	 */
	$errorhandler = new Error;

	/**
	 * Setup the Dependency Container of none was setup yet
	 */
	$dic = Dependency::setup();

	/**
	 * Get the Composer autoloader instance and allow the framework to use it
	 */
	$dic->inject('autoloader', self::$loader);

	/**
	 * Setup the shutdown, error & exception handlers
	 */
	$dic->inject('errorhandler', $errorhandler);

	/**
	 * Create the packages container, and load all already loaded ones
	 */
	$dic->register('packageprovider', function($container, $namespace, $paths = array())
	{
		// TODO: hardcoded class name
		return new PackageProvider($container, $namespace, $paths);
	});

	$dic->registerSingleton('packages', function($container)
	{
		// TODO: hardcoded class name
		return new DataContainer();
	});

	// create the packages container
	$packages = $dic->resolve('packages');

	// process all known composer libraries, and register them as Fuel packages
	foreach (self::$loader->getPrefixes() as $namespace => $paths)
	{
		// check if this package has a PackageProvider for us
		if (class_exists($class = trim($namespace, '\\').'\\Providers\\FuelPackageProvider'))
		{
			// load the package provider
			$provider = new $class($namespace, $paths);
		}
		else
		{
			// create a base provider instance
			$provider = $dic->resolve('packageprovider', array($namespace, $paths));
		}

		// validate the provider
		if ( ! $provider instanceOf PackageProvider)
		{
			throw new RuntimeException('PackageProvider for '.$namespace.' must be an instance of \Fuel\Foundation\PackageProvider');
		}

		// initialize the loaded package
		$provider->initPackage();

		// and store it in the container
		$packages->set($namespace, $provider);
	}

	// disable write access to the package container
	$packages->setReadOnly();

	/**
	 * Alias all Facades to global
	 */
	$dic->resolve('alias')->aliasNamespace('Fuel\Foundation\Facades', '');

	/**
	 * Create the global Config instance
	 */
	$config = $dic->resolve('config');
	$dic->inject('config.global', $config);

	// load the global framework configuration
	$config->addPath(APPSPATH);
	$config->load('config', null);

	/**
	 * Create the global Input instance
	 */
	$input = $dic->resolve('input');
	$dic->inject('input.global', $input);

	// import global data
	$input->fromGlobals();

	// assign the configuration container to this input instance
	$input->setConfig($config);

	/**
	 * Create the global Event instance
	 */
	$event = $dic->resolve('event');
	$dic->inject('event.global', $event);

	// setup a global shutdown event for this event container
	register_shutdown_function(function($event) { $event->trigger('shutdown'); }, $event);

	// setup a shutdown event for saving cookies and to cache the classmap
	InputClosureBindStupidWorkaround($event, $input, self::$loader);

	/**
	 * Do the remainder of the framework initialisation
	 */
	// TODO: not sure this belongs here
	Fuel::initialize($config);

	/**
	 * Run the global applications bootstrap, if present
	 */
	if (file_exists($file = APPSPATH.'bootstrap.php'))
	{
		$bootstrap = function($file) {
			include $file;
		};
		$bootstrap($file);
	}

	/**
	 * Alias all Base controllers to Fuel\Controller
	 */
	// TODO: move to a separate Fuel\Controller package?
	$dic->resolve('alias')->aliasNamespace('Fuel\Foundation\Controller', 'Fuel\Controller');
};

// call and cleanup
$bootstrapFuel(); unset($bootstrapFuel);
