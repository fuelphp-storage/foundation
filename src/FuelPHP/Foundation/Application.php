<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Foundation
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

namespace FuelPHP\Foundation;

/**
 * Application Base class
 *
 * Wraps an application package into an object to work with.
 *
 * @package  FuelPHP\Foundation
 *
 * @since  2.0.0
 */
class Application
{
	/**
	 * @var  int  keyname for Application packages
	 *
	 * @since  2.0.0
	 */
	const TYPE_APPLICATION = 0;

	/**
	 * @var  int  keyname for normal packages
	 *
	 * @since  2.0.0
	 */
	const TYPE_PACKAGE = 1000;

	/**
	 * @var  int  keyname for libraries (non routable, always last)
	 *
	 * @since  2.0.0
	 */
	const TYPE_LIBRARY = 100000;

	/**
	 * @var  Environment
	 *
	 * @since  2.0.0
	 */
	protected $env;

	/**
	 * @var  \FuelPHP\Foundation\Router  this applications router object once created
	 *
	 * @since  2.0.0
	 */
	protected $router;

	/**
	 * @var  \FuelPHP\Foundation\Request  contains the app main request object once created
	 *
	 * @since  2.0.0
	 */
	protected $request;

	/**
	 * @var  \FuelPHP\Foundation\Request  current active Request, not necessarily the main request
	 *
	 * @since  2.0.0
	 */
	protected $activeRequest;

	/**
	 * @var  array  active loaders in a prioritized list
	 *
	 * @since  2.0.0
	 */
	protected $packages = array(
		Application::TYPE_APPLICATION  => array(),
		Application::TYPE_PACKAGE      => array(),
		Application::TYPE_LIBRARY      => array(),
	);

	/**
	 * @var  array  active Application stack before activation of this one
	 *
	 * @since  2.0.0
	 */
	protected $activeApps = array();

	/**
	 * Constructor
	 *
	 * @since  2.0.0
	 */
	public function __construct($appName, $appPath)
	{
		// set the environment variable necessary for the package loader object
		$this->env = \FuelPHP\Foundation\Environment::singleton();

		// load the application package
		$this->loadPackage(array($appName, $appPath), Application::TYPE_APPLICATION);

		// load main Application config
// CHECKME

		// load the Security class
		$this->security = $this->env->forge('FuelPHP\Foundation\Security');
	}

	/**
	 * Execute the application main request
	 *
	 * @return  Application
	 * @throws  \Exception|\FuelPHP\Foundation\Request\Exception|\FuelPHP\Foundation\Request\Exception\NotFound
	 *
	 * @since  2.0.0
	 */
	public function execute()
	{
		$this->activate();

		// create a router object
		$this->router = $this->env->forge('\FuelPHP\Foundation\Router');

		// Start output buffer
// CHECKME
//		ob_start($this->config->get('obCallback', null));

		try
		{
			// Execute the request
			$this->request->execute();
		}
		catch (Exception\NotFound $e)
		{
			$this->request->response = $this->notFoundResponse($e);
		}
		catch (Exception\Base $e)
		{
			$this->request->response = $this->errorResponse($e);
		}
		catch (\Exception $e)
		{
			// deactivate and rethrow
			$this->deactivate();
			throw $e;
		}

		// Check if request needs to be assigned an deactivate
		method_exists($this->request->response, '_setRequest')
			and $this->request->response->_setRequest($this->request);
		$this->deactivate();

		return $this;
	}

	/**
	 * Create the application main request
	 *
	 * @param   string  $uri
	 * @param   array|\FuelPHP\Foundation\Input  $input
	 * @return  Base
	 *
	 * @since  2.0.0
	 */
	public function request($uri, $input = array())
	{
		$this->request = $this->env->forge('FuelPHP\Foundation\Request', null, $this->security->cleanUri($uri), $input);
		return $this;
	}

	/**
	 * Fetch the Request object
	 *
	 * @return  \FuelPHP\Foundation\Request
	 * @throws  \RuntimeException
	 *
	 * @since  2.0.0
	 */
	public function getRequest()
	{
		if ( ! isset($this->request))
		{
			throw new \RuntimeException('Request needs to be made before the object may be fetched.');
		}

		return $this->request;
	}

	/**
	 * Return the response object
	 *
	 * @return  \FuelPHP\Foundation\Response
	 *
	 * @since  2.0.0
	 */
	public function getResponse()
	{
		return $this->request->getResponse();
	}

	/**
	 * Return the router object
	 *
	 * @return  \FuelPHP\Foundation\Router
	 *
	 * @since  2.0.0
	 */
	public function getRouter()
	{
		return $this->router;
	}

	/**
	 * Sets the current active request
	 *
	 * @param   \FuelPHP\Foundation\Request  $request
	 * @return  Base
	 *
	 * @since  2.0.0
	 */
	public function setActiveRequest(Request $request = null)
	{
		$this->activeRequest = $request;
		return $this;
	}

	/**
	 * Returns current active Request
	 *
	 * @return  \FuelPHP\Foundation\Request
	 *
	 * @since  2.0.0
	 */
	public function getActiveRequest()
	{
		return $this->activeRequest;
	}

	/**
	 * Makes this Application the active one
	 *
	 * @return  Application  for method chaining
	 *
	 * @since  2.0.0
	 */
	public function activate()
	{
		array_push($this->activeApps, $this->env->getActiveApplication());
		$this->env->setActiveApplication($this);
		return $this;
	}

	/**
	 * Deactivates this Application and reactivates the previous active
	 *
	 * @return  Application  for method chaining
	 *
	 * @since  2.0.0
	 */
	public function deactivate()
	{
		$this->env->setActiveApplication(array_pop($this->activeApps));
		return $this;
	}

	/**
	 * Adds a package
	 *
	 * @param   string|Loader       $name
	 * @param   int                 $type
	 *
	 * @throws  \RuntimeException
	 *
	 * @return  Loader              for method chaining
	 *
	 * @since  2.0.0
	 */
	public function loadPackage($name, $type = Application::TYPE_PACKAGE)
	{
		// return directly when already loaded
		if ($this->packageExists($name, $type))
		{
			return $this->getPackage($name, $type);
		}

		// directly add an unnamed package
		if ($name instanceof Package)
		{
			$package = $name;
			$name = uniqid();
		}
		// directly add a named package: array($name, $package)
		elseif (is_array($name) and end($name) instanceof Package)
		{
			$package = end($name);
			$name = reset($name);
		}
		// add a package using a name, or using array($name, $fullpath)
		else
		{
			! is_array($name) and $name = array($name, $this->env->getPath($name).$name.'/');
			list($name, $path) = $name;

			// check if the package hasn't already been loaded
			if (isset($this->packages[$type][$name]))
			{
				throw new \RuntimeException('Package already loaded, can\'t be loaded twice.');
			}

			// deal with paths to the package folder instead of the package
			$path = rtrim($path, '\/').'/';
			is_dir($path.$name) and $path = $path.$name.'/';

			// check if we have a package loader
			if ( ! file_exists($path.'loader.php'))
			{
				throw new \RuntimeException('No Package loader could be found in '.$path);
			}

			// fetch the Package loader
			$package = require $path.'loader.php';
			if ( ! $package instanceof Package)
			{
				throw new \RuntimeException('Package loader must return an instance of FuelPHP\\Foundation\\Package');
			}
			$package->setApp($this);
		}

		// register the path with the environment
		$this->env->addPath($name, $package->getPath(), true);

		// and mark the Package as loaded
		$this->packages[$type] = array($name => $package->setName($name)) + $this->packages[$type];

		return $package;
	}

	/**
	 * Check if a package is loaded already
	 *
	 * @param   string|array|Loader     $name
	 * @param   int                     $type
	 *
	 * @return  bool
	 */
	public function packageExists($name, $type = Application::TYPE_PACKAGE)
	{
		// Ensure the name is a string
		is_string($name) or $name = is_array($name) ? reset($name) : $name->name;

		return isset($this->packages[$type][$name]);
	}

	/**
	 * Fetch a specific package
	 *
	 * @param   string  $name
	 * @param   int     $type
	 *
	 * @throws  \OutOfBoundsException
	 *
	 * @return  Loader
	 *
	 * @since  2.0.0
	 */
	public function getPackage($name, $type = Application::TYPE_PACKAGE)
	{
		if ( ! $this->packageExists($name, $type))
		{
			throw new \OutOfBoundsException('Unknown package: '.$name);
		}

		return $this->packages[$type][$name];
	}

	/**
	 * Fetch all packages or just those of a specific type
	 *
	 * @param   int|null  $type  null for all, int for a specific type
	 *
	 * @throws  \OutOfBoundsException
	 *
	 * @return  array
	 *
	 * @since  2.0.0
	 */
	public function getPackages($type = null)
	{
		if (is_null($type))
		{
			return $this->packages;
		}
		elseif ( ! isset($this->packages[$type]))
		{
			throw new \OutOfBoundsException('Unknown package type: '.$type);
		}

		return $this->packages[$type];
	}

	/**
	 * Locate a specific type of class
	 *
	 * @param   string  $type
	 * @param   string  $className
	 * @param   bool    $routing  whether this search is in routing context
	 *
	 * @return  bool|string  the controller classname or false on failure
	 *
	 * @since  2.0.0
	 */
	public function findClass($type, $className, $routing = false)
	{
		// scan through the loaded packages to find the requested class
		foreach ($this->packages as $pkgs)
		{
			foreach ($pkgs as $pkg)
			{
				if (( ! $routing or $pkg->getRoutable())
					and ($found = $pkg->findClass($type, $className)))
				{
					return $found;
				}
			}
		}

		// all is lost
		return false;
	}

	/**
	 * Allows setting a response object for NotFound errors or executing a fallback
	 *
	 * @param   \FuelPHP\Foundation\Request\Exception\NotFound  $e
	 * @return  \FuelPHP\Foundation\Response\Base
	 * @throws  \FuelPHP\Foundation\Request\Exception\NotFound
	 */
	protected function notFoundResponse(Exception\NotFound $e)
	{
		throw $e;
	}

	/**
	 * Allows setting a response object for errors or executing a fallback
	 *
	 * @param   \FuelPHP\Foundation\Request\Exception\Base  $e
	 * @return  \FuelPHP\Foundation\Response\Base
	 * @throws  \FuelPHP\Foundation\Request\Exception\Base
	 */
	protected function errorResponse(Exception\Base $e)
	{
		throw $e;
	}
}
