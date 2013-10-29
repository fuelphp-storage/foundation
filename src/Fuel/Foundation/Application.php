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
use Fuel\Config\Container as ConfigContainer;

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
	 * @var  array  namespace to location mappings
	 *
	 * @since  2.0.0
	 */
	protected $appNamespaces = array();

	/**
	 * @var  ApplicationInjectionFactory  this applications object factory
	 *
	 * @since  2.0.0
	 */
	protected $factory;

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
	 * @var  Fuel/Foundation/Input  this applications input object
	 *
	 * @since  2.0.0
	 */
	protected $input;

	/**
	 * @var  array  this applications list of languages objects
	 *
	 * @since  2.0.0
	 */
	protected $languages = array();

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
	 * @param  string                       $appName      name of this application
	 * @param  string                       $appPath      path to the application installation root
	 * @param  string                       $namespace    this applications base namespace
	 * @param  string                       $environment  the environment this application has to run in
	 * @param  ApplicationInjectionFactory  $factory      factory object to construct external objects

	 * @since  2.0.0
	 */
	public function __construct($appName, $appPath, $namespace, $environment, ApplicationInjectionFactory $factory)
	{
		// store the applications object factory
		$this->factory = $factory;

		// register the application
		$this->factory->registerApplication($this);

		// store the application name
		$this->appName = $appName;

		// and it's base namespace
		$this->appNamespace = $namespace;

		// check if the path is valid, and if so, store it
		if ( ! is_dir($appPath))
		{
			throw new \InvalidArgumentException('FOU-008: Application path ['.$appPath.'] does not exist.');
		}
		$this->appPath = realpath($appPath).DS;

		// store it in the application namespaces list
		$this->appNamespaces[$this->appNamespace] = array(
			'prefix' => false,
			'namespace' => $this->appNamespace,
			'path' => $this->appPath,
			'routeable' => true,
		);

		// does this app have a bootstrap?
		if (file_exists($file = $this->appPath.'bootstrap.php'))
		{
			$loadbootstrap = function($app, $__file__) {
				return include $__file__;
			};
			$loadbootstrap($this, $file);
		}

		// setup the configuration container...
		$this->config = $factory->createConfigContainer($this->appName)
			->addPath(realpath(__DIR__.DS.'..'.DS.'..'.DS.'..'.DS.'defaults').DS)
			->addPath($this->appPath);

		// and load the application config
		$this->config->load('config', null);

		// load the file config
		$this->config->load('file', true);

		// load the lang config
		$this->config->load('lang', true);

		// setup the input container...
		$this->input = $factory->createInputContainer();

		// create the environment for this application
		$this->env = $factory->createEnvironmentContainer($this, $environment);

		// create the log instance for this application
		$this->log = $factory->createLogInstance('fuelphp-'.$this->appName);

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
		$this->event = $factory->createEventInstance();

		// setup a shutdown event for this application
		register_shutdown_function(function($event) { $event->trigger('shutdown', $this); }, $this->event);

		// load the session config
		$session = $this->config->load('session', true);

		// do we need to auto-start one?
		if (isset($session['auto_initialize']) and $session['auto_initialize'])
		{
			// create a session instance
			$this->session = $factory->createSessionInstance();
		}

		// create the view manager instance for this application
		$this->viewManager = $factory->createViewmanagerInstance($this->appName, $this->appPath);

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
			$this->viewManager->registerParser($extension, $factory->createViewParserInstance($parser));
		}

		// create a router object
		$this->router = $factory->createRouterInstance($this->appName);

		// and load any defined routes
		$this->loadRoutes($this->appPath, $this->appNamespaces[$this->appNamespace]);

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

		throw new \OutOfBoundsException('FOU-009: Property ['.$property.'] not available on the application.');
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
	 * Return the application namespaces
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getNamespaces()
	{
		return $this->appNamespaces;
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
	 * Return the applications Input instance
	 *
	 * @return  Input
	 *
	 * @since  2.0.0
	 */
	public function getInput()
	{
		return $this->input;
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
	 * Return a language container instance
	 *
	 * @return  Fuel\Config\Container
	 *
	 * @since  2.0.0
	 */
	public function getLanguage($language = null)
	{
		if ($language === null)
		{
			$language = $this->config->get('lang.current', $this->config->get('lang.fallback', 'en'));
		}

		if ( ! isset($this->languages[$language]))
		{
			$this->languages[$language] = $this->factory->createLanguageInstance($this->appName.'-'.$language);
			$this->languages[$language]
				->addPath(realpath(__DIR__.DS.'..'.DS.'..'.DS.'..'.DS.'defaults'.DS.'lang'.DS.$language).DS)
				->addPath($this->appPath.'lang'.DS.$language.DS);
		}

		return $this->languages[$language];
	}

	/**
	 * Set an applications language container instance
	 *
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function setLanguage($language, ConfigContainer $instance)
	{
		$this->languages[$language] = $instance;
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
		$uri === null and $uri = $this->input->getPathInfo();

		// log the request
		$this->log->info('Application "'.$this->appName.'" is creating new "'.$this->input->getMethod().'" Request for URI: '.(empty($uri) ? '/' : $uri));

		// forge a new request
		return $this->factory->createRequestInstance($uri, $input);
	}

	/**
	 * Add a module to the application
	 *
	 * @param  string   URI prefix for this module
	 * @param  string   root namespace for classes in this module
	 * @param  string   the path to the root of the module
	 * @param  boolean  whether or not this module is routable
	 *
	 * @return Application  for chaining
	 */
	public function addModule($prefix, $namespace, $path, $routeable = true)
	{
		if ( ! is_dir($path))
		{
			throw new \InvalidArgumentException('FOU-010: Module path ['.$path.'] does not exist.');
		}
		$path = realpath($path).DS;

		// store it in the application namespaces list
		$this->appNamespaces[$prefix] = array(
			'prefix' => $prefix,
			'namespace' => $namespace,
			'path' => $path,
			'routeable' => $routeable,
		);

		// make sure longer prefixes are first in the list
		krsort($this->appNamespaces);

		// and load any defined routes in this module
		$this->loadRoutes($path, $this->appNamespaces[$prefix]);

		// does this module have a bootstrap?
		if (file_exists($file = $path.'bootstrap.php'))
		{
			$loadbootstrap = function($app, $__file__) {
				return include $__file__;
			};
			$loadbootstrap($this, $file);
		}

		return $this;
	}

	/**
	 * Add all modules in the given path to the application
	 *
	 * @param  string   the path to the root of the modules
	 *
	 * @return Application  for chaining
	 */
	public function addModulePath($path)
	{
		if ( ! is_dir($path))
		{
			throw new \InvalidArgumentException('FOU-011: Module path ['.$path.'] does not exist.');
		}

		$folder = new \GlobIterator(realpath($path).DS.'*', \GlobIterator::SKIP_DOTS | \GlobIterator::CURRENT_AS_PATHNAME);

		foreach($folder as $entry)
		{
			// make sure it's a directory, and we have a classes folder
			if (is_dir($entry) and is_dir($entry.DS.'classes'))
			{
				$this->addModule(basename($entry), ucfirst(basename($entry)), $entry, true);
			}
		}
	}

	/**
	 * Import routes from the given path's config folder, it defined
	 *
	 * @param  string  path to an app/module/package root
	 * @param  array   information about the environment these routes are defined in
	 *
	 * @return  bool  Whether or not the path had routes defined
	 */
	protected function loadRoutes($path, Array $config = array())
	{
		if (file_exists($path = $path.'config'.DS.'routes.php'))
		{
			$loadroutes = function($router, $config, $__file__) {
				return include $__file__;
			};
			$routes = $loadroutes($this->router, $config, $path);

			if (is_array($routes))
			{
				// TODO, process v1.x type route array
			}

			return true;
		}

		return false;
	}
}
