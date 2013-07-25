<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation;

/**
 * Application Base class
 *
 * Wraps an application package into an object to work with.
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Application
{
	/**
	 * @var  string  name of this application
	 *
	 * @since  2.0.0
	 */
	protected $appName;

	/**
	 * @var  string  application root path
	 *
	 * @since  2.0.0
	 */
	protected $appPath;

	/**
	 * @var  string  base namespace for this application
	 *
	 * @since  2.0.0
	 */
	protected $appNamespace;

	/**
	 * @var  Router  this applications router object
	 *
	 * @since  2.0.0
	 */
	protected $router;

	/**
	 * @var  Request  contains the app main request object
	 *
	 * @since  2.0.0
	 */
	protected $request;

	/**
	 * @var  array  current active request stack
	 *
	 * @since  2.0.0
	 */
	protected $requests = array();

	/**
	 * Constructor
	 *
	 * @since  2.0.0
	 */
	public function __construct($appName, $appPath, $namespace, $environment)
	{
		// store the application name
		$this->appName = $appName;

		// and it's base namespace
		$this->appNamespace = $namespace;

		// check if the path is valid, and if so, store it
		if ( ! is_dir($appPath))
		{
			throw new \InvalidArgumentException('Application path "'.$appPath.'" does not exist.');
		}
		$this->appPath = realpath($appPath).DS;

		// setup the configuration container, and load the application config
		\Config::forge($this->appName)
			->addPath($this->appPath.'config'.DS)
			->setParent(\Config::get())
			->load('config', null);

		// create the environment for this application
		\Environment::forge($this, $environment);

		// create the view manager instance for this application
		$viewmanager = \ViewManager::forge($this);

		// load the view config
		\Config::get($this->appName)
			->load('view');

		// get the defined view parsers
		$parsers = $this->getConfig()->get('parsers', array());

		// and register them to the View Manager
		foreach($parsers as $extension => $parser)
		{
			if (is_numeric($extension))
			{
				$extension = $parser;
				$parser = 'parser.'.$extension;
			}
			$viewmanager->registerParser($extension, \Dependency::resolve($parser));
		}

		// TODO: create a router object
		$this->router = \Dependency::resolve('Fuel\Foundation\Router', array($this));
	}

	/**
	 * Get a property that is available through a getter
	 *
	 * @param   string  $property
	 * @return  mixed
	 * @throws  \OutOfBoundsException
	 *
	 * @since  2.0.0
	 */
	public function __get($property)
	{
		if (method_exists($this, $method = 'get'.ucfirst($property)))
		{
			return $this->{$method}();
		}

		throw new \OutOfBoundsException('Property "'.$property.'" not available on the application.');
	}

	/**
	 * Returns the applications config object
	 *
	 * @return  Fuel\Config\Datacontainer
	 *
	 * @since  2.0.0
	 */
	public function getConfig()
	{
		return \Config::get($this->appName);
	}

	/**
	 * Returns the applications environment object
	 *
	 * @return  Fuel\Config\Datacontainer
	 *
	 * @since  2.0.0
	 */
	public function getEnvironment()
	{
		return \Environment::get($this->appName);
	}


	/**
	 * Construct an application request
	 *
	 * @param   string  $uri
	 * @param   array|Input  $input
	 *
	 * @return  Request
	 *
	 * @since  2.0.0
	 */
	public function getRequest($uri = null, Array $input = array())
	{
		// if no uri is given, fetch the global one
		$uri === null and $uri = \Input::getInstance()->getPathInfo($this->getEnvironment()->baseUrl);

		// forge a new request
		return \Request::forge($this, \Security::cleanUri($uri), $input);
	}

	/**
	 * Return the router object
	 *
	 * @return  Router
	 *
	 * @since  2.0.0
	 */
	public function getRouter()
	{
		return $this->router;
	}

	/**
	 * Return the application name
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getName()
	{
		return $this->appName;
	}

	/**
	 * Return the application base namespace
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getNamespace()
	{
		return $this->appNamespace;
	}

	/**
	 * Return the application root path
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getPath()
	{
		return $this->appPath;
	}

	/**
	 * Return the applications View manager
	 *
	 * @return  Fuel\Display\ViewManager
	 *
	 * @since  2.0.0
	 */
	public function getViewManager()
	{
		return \ViewManager::get($this->appName);
	}

	/**
	 * Sets the current active request
	 *
	 * @param   Request  $request
	 *
	 * @return  Application
	 *
	 * @since  2.0.0
	 */
	public function setActiveRequest(Request $request = null)
	{
		$this->requests[] = $request;
		return $this;
	}

	/**
	 * Returns current active Request
	 *
	 * @return  Request
	 *
	 * @since  2.0.0
	 */
	public function getActiveRequest()
	{
		return empty($this->requests) ? null : end($this->requests);
	}

	/**
	 * Resets the current active request
	 *
	 * @return  Application
	 *
	 * @since  2.0.0
	 */
	public function resetActiveRequest()
	{
		if ( ! empty($this->requests))
		{
			array_pop($this->requests);
		}
		return $this;
	}
}
