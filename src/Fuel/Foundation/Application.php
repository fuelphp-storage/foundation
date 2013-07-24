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
	 * @var  Fuel\Config  this applications config container
	 *
	 * @since  2.0.0
	 */
	protected $config;

	/**
	 * @var  Environment  this applications environment
	 *
	 * @since  2.0.0
	 */
	protected $environment;

	/**
	 * @var  Security  this applications security container
	 *
	 * @since  2.0.0
	 */
	protected $security;

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
	 * @var  Request  current active Request, not necessarily the main request
	 *
	 * @since  2.0.0
	 */
	protected $activeRequest;

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

		// and setup the configuration container
		$this->config = \Fuel::resolve('config');
		$this->config->addPath($this->appPath);
		$this->config->setParent(\Fuel::getConfig());

		// create the environment for this application
		$this->environment = \Fuel::resolve('environment', array($this, $environment, $this->config));

		// create the security container for this application
		$this->security = \Fuel::resolve('security', array($this));

		// create a router object
		$this->router = \Fuel::resolve('Fuel\Foundation\Router', array($this));
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
		return $this->config;
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
		return $this->environment;
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
	public function request($uri = null, Array $input = array())
	{
		// if no uri is given, fetch the global one
		$uri === null and $uri = \Fuel::getInput()->getPathInfo($this->environment->baseUrl);

		$this->request = \Fuel::resolve('request', array($this, $this->security->cleanUri($uri), $input));

		return $this;
	}

	/**
	 * Execute the application main request
	 *
	 * @throws  \Exception|\Exception|\Exception\NotFound
	 *
	 * @return  Application
	 *
	 * @since  2.0.0
	 */
	public function execute()
	{
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
			// rethrow
			throw $e;
		}

		// Check if request needs to be assigned
		method_exists($this->request->getResponse(), '_setRequest')
			and $this->request->getResponse()->_setRequest($this->request);

		return $this;
	}

	/**
	 * Fetch the Request object
	 *
	 * @throws  \RuntimeException
	 *
	 * @return  Request
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
	 * @return  Response
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
	 * Sets the current active request
	 *
	 * @param   Request  $request
	 *
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
	 * @return  Request
	 *
	 * @since  2.0.0
	 */
	public function getActiveRequest()
	{
		return $this->activeRequest;
	}

	/**
	 * Allows setting a response object for NotFound errors or executing a fallback
	 *
	 * @param   Exception\NotFound  $e
	 *
	 * @throws  Exception\NotFound
	 *
	 * @return  Response\Base
	 */
	protected function notFoundResponse(Exception\NotFound $e)
	{
		throw $e;
	}

	/**
	 * Allows setting a response object for errors or executing a fallback
	 *
	 * @param   Exception\Base  $e
	 *
	 * @throws  Exception\Base
	 *
	 * @return  Response\Base
	 */
	protected function errorResponse(Exception\Base $e)
	{
		throw $e;
	}
}
