<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation;

use Fuel\Foundation\Fuel;
use Fuel\Session\Manager as SessionManager;

/**
 * Defines the FuelPHP application, and provides the applictions global environment
 */
class Application
{
	/**
	 * @var boolean
	 */
	protected $initialized = false;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $appNamespace;

	/**
	 * @var string
	 */
	protected $environmentName;

	/**
	 * @var InjectionFactory
	 */
	protected $factory;

	/**
	 * List or URI to component mappings
	 *
	 * @var array
	 */
	protected $components = [];

	/**
	 * @var Component
	 */
	protected $component;

	/**
	 * @var Environment
	 */
	protected $environment;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $log;

	/**
	 * @var \League\Event\EmitterInterface
	 */
	protected $event;

	/**
	 * @var SessionManager
	 */
	protected $session;

	/**
	 * @var \Fuel\Display\ViewManager
	 */
	protected $viewManager;

	/**
	 * @param string           $name
	 * @param string           $appNamespace
	 * @param string           $environment
	 * @param InjectionFactory $factory
	 */
	public function __construct($name, $appNamespace, $environment = 'development', InjectionFactory $factory)
	{
		$this->name = $name;
		$this->appNamespace = $appNamespace;
		$this->environmentName = $environment;
		$this->factory = $factory;
	}

	public function initialize()
	{
		if ($this->initialized)
		{
			throw new \LogicException('Application is already initialized');
		}

		$this->initialized = true;

		// create the main application component
		$this->component = $this->newComponent('/', $this->appNamespace);

		// create the environment for this application
		$this->environment = $this->factory->createEnvironmentContainer($this->name, $this->environmentName, $this);

		// create the log instance for this application
		$this->log = $this->factory->createLogInstance('fuel');

		// load the log config
		$log = $this->getConfig()->load('log', true);

		// a log customizer defined?
		if (isset($log['customize']) and $log['customize'] instanceOf \Closure)
		{
			$log['customize']($this, $this->log);
		}

		// setup the event container
		$this->event = $this->factory->createEventInstance();

		$shutdown = new Event\Shutdown($this);

		// setup a global shutdown event for this event container
		register_shutdown_function(function($event, $shutdown)
		{
			$event->emit($shutdown);
		}, $this->event, $shutdown);

		// setup a shutdown event for writing cookies
		$this->event->addListener('shutdown', function($shutdown)
		{
			$shutdown->getApp()->getRootComponent()->getInput()->getCookie()->send();
		});

		// load the session config
		$session = $this->getConfig()->load('session', true);

		// do we need to auto-start one?
		if (isset($session['auto_initialize']) and $session['auto_initialize'])
		{
			// create a session instance
			$this->session = $this->factory->createSessionInstance();
		}

		// create the view manager instance for this application
		$this->viewManager = $this->factory->createViewmanagerInstance();

		// load the view config
		$this->getConfig()->load('view', true);

		// get the defined view parsers
		$parsers = $this->getConfig()->get('view.parsers', []);

		// and register them to the View Manager
		foreach($parsers as $extension => $parser)
		{
			if (is_numeric($extension))
			{
				$extension = $parser;
				$parser = 'parser.'.$extension;
			}
			$this->viewManager->registerParser($extension, $this->factory->createViewParserInstance($parser));
		}

		// log we're alive!
		$this->log->info('Application initialized.');
	}

	/**
	 * Creates a new application component
	 *
	 * @param string           $uri
	 * @param string           $namespace
	 * @param boolean          $routeable
	 * @param string|array     $paths
	 * @param string|Component $parent
	 *
	 * @return Component
	 */
	public function newComponent($uri, $namespace, $routeable = true, $paths = null, $parent = null)
	{
		// unify the URI
		$uri = trim($uri, '/');

		// and the namespace
		$namespace = trim($namespace, '\\');

		// and whether or not the component is routeable
		$routeable = (bool) $routeable;

		// resolve the parent if needed
		if (is_string($parent) and isset($this->components[$parent]))
		{
			$parent = $this->components[$parent];
		}

		// create the component instance, store it, and return it
		$this->components[$uri] = $this->factory->createComponentInstance($this, $uri, $namespace, $paths, $routeable, $parent);

		// sort it so it's easier to do URI lookups
		krsort($this->components);

		// and return the component we've created
		return $this->components[$uri];
	}

	/**
	 * Checks whether the application is initialized
	 *
	 * @return boolean
	 */
	public function isInitialized()
	{
		return $this->initialized;
	}

	/**
	 * Returns the applications environment string
	 *
	 * @return string
	 */
	public function environment()
	{
		return $this->environmentName;
	}

	/**
	 * Returns a property that is available through a getter
	 *
	 * @param string $property
	 *
	 * @return mixed
	 *
	 * @throws \OutOfBoundsException
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
	 * Returns the applications root component object
	 *
	 * @return Component
	 */
	public function getRootComponent()
	{
		return $this->component;
	}

	/**
	 * Returns a named registered applications component object
	 *
	 * @return Component
	 */
	public function getComponent($uri)
	{
		return isset($this->components[$uri]) ? $this->components[$uri] : null;
	}

	/**
	 * Returns all applications component objects
	 *
	 * @return Component[]
	 */
	public function getComponents()
	{
		return $this->components;
	}

	/**
	 * Returns the applications config object
	 *
	 * @return Fuel\Config\Container
	 */
	public function getConfig()
	{
		return $this->getRootComponent()->getConfig();
	}

	/**
	 * Returns the applications environment object
	 *
	 * @return Environment
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	/**
	 * Return the applications event manager
	 *
	 * @return \League\Event\Emitter
	 */
	public function getEvent()
	{
		return $this->event;
	}

	/**
	 * Return the applications Input instance
	 *
	 * @return Input
	 */
	public function getInput()
	{
		return $this->getRootComponent()->getInput();
	}

	/**
	 * Return the applications Logger
	 *
	 * @return \Psr\Log\LoggerInterface
	 */
	public function getLog()
	{
		return $this->log;
	}

	/**
	 * Return the defined application name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Return the applications session manager
	 *
	 * @return SessionManager
	 */
	public function getSession()
	{
		return $this->session;
	}

	/**
	 * Sets the applications session manager
	 *
	 * @param $session SessionManager
	 */
	public function setSession(SessionManager $session)
	{
		$this->session = $session;
	}

	/**
	 * Return the applications View manager
	 *
	 * @return \Fuel\Display\ViewManager
	 */
	public function getViewManager()
	{
		return $this->viewManager;
	}
}
