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

use Fuel\Session\Manager as SessionManager;

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
	 * @var  Environment  this applications environment object
	 *
	 * @since  2.0.0
	 */
	protected $env;

	/**
	 * @var  Psr/Log/LoggerInterface  this applications log object
	 *
	 * @since  2.0.0
	 */
	protected $log;

	/**
	 * @var  Fuel/Config/Container  this applications config object
	 *
	 * @since  2.0.0
	 */
	protected $config;

	/**
	 * @var  Fuel/Session/Manager  this applications session object
	 *
	 * @since  2.0.0
	 */
	protected $session;

	/**
	 * @var  Fuel/Event/Container  this applications event container
	 *
	 * @since  2.0.0
	 */
	protected $event;

	/**
	 * @var  Fuel/Display/ViewManager  this applications view manager object
	 *
	 * @since  2.0.0
	 */
	protected $viewManager;

	/**
	 * @var  Router  this applications router object
	 *
	 * @since  2.0.0
	 */
	protected $router;

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

		// setup the configuration container...
		$this->config = \Config::forge($this->appName)
			->addPath($this->appPath.'config'.DS)
			->setParent(\Config::getConfig());

		// and load the application config
		$this->config->load('config', null);

		// load the file config
		$this->config->load('file', true);

		// create the environment for this application
		$this->env = \Environment::forge($this, $environment);

		// create the log instance for this application
		$this->log = \Log::forge('fuelphp-'.$this->appName);

		// load the log config
		if (file_exists($path = $this->appPath.'config'.DS.'log.php'))
		{
			$loadlog = function($log, $app, $__file__) {
				return require $__file__;
			};
			$log = $loadlog($this->log, $this, $path);

			// if a log instance is returned, replace the one we had
			if ($log instanceOf \Psr\Log\LoggerInterface)
			{
				$this->log = $log;
			}
		}

		// setup the applications event manager
		$this->event = \Event::forge();

		//
		register_shutdown_function(function($event) { $event->trigger('shutdown', $this); }, $this->event);

		// load the session config
		$session = $this->config->load('session', true);

		// do we need to auto-start one?
		if (isset($session['auto_initialize']) and $session['auto_initialize'])
		{
			// create a session instance
			$this->session = \Session::forge($session);

			// start the session
			$this->session->start();

			// and make sure it ends too
			$this->event->on('shutdown', function($event, $app) { $app->getSession()->stop(); });
		}

		// create the view manager instance for this application
		$this->viewManager = \Dependency::multiton('viewmanager', $this->appName, array(
			\Dependency::resolve('finder', array(
				array($this->appPath),
			)),
			array(
				'cache' => $this->appPath.'cache',
			)
		));

		// load the view config
		$this->config->load('view', true);

		// get the defined view parsers
		$parsers = $this->config->get('view.parsers', array());

		// and register them to the View Manager
		foreach($parsers as $extension => $parser)
		{
			if (is_numeric($extension))
			{
				$extension = $parser;
				$parser = 'parser.'.$extension;
			}
			$this->viewManager->registerParser($extension, \Dependency::resolve($parser));
		}

		// create a router object
		$this->router = \Router::forge($this);

		// and load any defined routes
		if (file_exists($path = $this->appPath.'config'.DS.'routes.php'))
		{
			$loadroutes = function($router, $__file__) {
				return include $__file__;
			};
			$routes = $loadroutes($this->router, $path);

			if (is_array($routes))
			{
				// TODO, process v1.x type route array
			}
		}

		// log we're alive!
		$this->log->info('Application "'.$this->appName.'" initialized.');
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
		return $this->env;
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
	 * Return the applications View manager
	 *
	 * @return  Fuel\Display\ViewManager
	 *
	 * @since  2.0.0
	 */
	public function getViewManager()
	{
		return $this->viewManager;
	}

	/**
	 * Return the applications Log manager
	 *
	 * @return  Psr\Log\LoggerInterface
	 *
	 * @since  2.0.0
	 */
	public function getLog()
	{
		return $this->log;
	}

	/**
	 * Return the applications session manager
	 *
	 * @return  Fuel\Session\Manager
	 *
	 * @since  2.0.0
	 */
	public function getSession()
	{
		return $this->session;
	}

	/**
	 * Set the applications session manager
	 *
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function setSession(SessionManager $session)
	{
		$this->session = $session;
	}

	/**
	 * Return the applications event manager
	 *
	 * @return  Fuel\Event\Container
	 *
	 * @since  2.0.0
	 */
	public function getEvent()
	{
		return $this->event;
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
		$uri === null and $uri = \Input::getInstance()->getPathInfo();

		// log the request
		$this->log->info('Application "'.$this->appName.'" is creating new Request for URI: '.(empty($uri) ? '/' : $uri));

		// forge a new request
		return \Request::forge($this, \Security::cleanUri($uri), $input);
	}
}
