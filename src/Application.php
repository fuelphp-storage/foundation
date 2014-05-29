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

/**
 * Application class
 *
 * Defines the FuelPHP application, and provides the applictions global environment
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Application
{
	/**
	 * @var  InjectionFactory  this applications object factory
	 *
	 * @since  2.0.0
	 */
	protected $factory;

	/**
	 * @var  string  name of this application
	 *
	 * @since  2.0.0
	 */
	protected $_name;

	/**
	 * @var  array  list or URI to component mappings
	 *
	 * @since  2.0.0
	 */
	protected $_components = array();

	/**
	 * @var  Fuel/Event/Container  this applications event container
	 *
	 * @since  2.0.0
	 */
	protected $_event;

	/**
	 * @var  Fuel/Session/Manager  this applications session object
	 *
	 * @since  2.0.0
	 */
	protected $_session;

	/**
	 * @var  Fuel/Display/ViewManager  this applications view manager object
	 *
	 * @since  2.0.0
	 */
	protected $_viewManager;

	/**
	 * @var  Component  this applications main component object
	 *
	 * @since  2.0.0
	 */
	protected $_component;

	/**
	 * @var  Environment  this applications environment object
	 *
	 * @since  2.0.0
	 */
	protected $_environment;

	/**
	 * @var  Psr/Log/LoggerInterface  this applications log object
	 *
	 * @since  2.0.0
	 */
	protected $_log;

	/**
	 * Constructor
	 *
	 * @param  string            $appNamespace  this applications base namespace
	 * @param  string            $environment   this applications runtime environment
	 * @param  InjectionFactory  $factory       factory object to construct external objects
	 *
	 * @since  2.0.0
	 */
	public function __construct($name, $appNamespace, $environment = 'development', InjectionFactory $factory)
	{
		// store the applications object factory
		$this->factory = $factory;

		// store the application name
		$this->_name = $name;

		// create the main application component
		$this->_component = $this->newComponent('/', $appNamespace);

		// create the environment for this application
		$this->_environment = $this->factory->createEnvironmentContainer($name, $environment, $this);

		// create the log instance for this application
		$this->_log = $this->factory->createLogInstance('fuel');

		// load the log config
		$log = $this->getConfig()->load('log', true);

		// a log customizer defined?
		if (isset($log['customize']) and $log['customize'] instanceOf \Closure)
		{
			$log['customize']($this, $this->_log);
		}

		// setup the event container...
		$this->_event = $this->factory->createEventInstance();

		// setup a global shutdown event for this event container
		register_shutdown_function(function($event) { $event->trigger('shutdown'); }, $this->_event);

		// setup a shutdown event for writing cookies
		$this->event->on('shutdown', function($event) { $this->getCookie()->send(); }, $this->_component->getInput());

		// load the session config
		$session = $this->getConfig()->load('session', true);

		// do we need to auto-start one?
		if (isset($session['auto_initialize']) and $session['auto_initialize'])
		{
			// create a session instance
			$this->_session = $factory->createSessionInstance();
		}

		// create the view manager instance for this application
		$this->_viewManager = $factory->createViewmanagerInstance();

		// load the view config
		$this->getConfig()->load('view', true);

		// get the defined view parsers
		$parsers = $this->getConfig()->get('view.parsers', array());

		// and register them to the View Manager
		foreach($parsers as $extension => $parser)
		{
			if (is_numeric($extension))
			{
				$extension = $parser;
				$parser = 'parser.'.$extension;
			}
			$this->_viewManager->registerParser($extension, $factory->createViewParserInstance($parser));
		}

		// log we're alive!
		$this->_log->info('Application initialized.');
	}

	/**
	 * Construct a new application component
	 *
	 * @param  string            $uri               base URI for this component
	 * @param  string            $namespace         namespace that identifies this component
	 * @param  string|array      $paths             optional Path or paths to the root folder of the component
	 * @param  boolean           $routeable         whether or not this component is publicly routable
	 * @param  string|Component  $parent            Parent component, or URI of the parent Component
	 *
	 * @return  Component  the newly constucted component object
	 *
	 * @since  2.0.0
	 */
	public function newComponent($uri, $namespace, $paths = null, $routeable = true, $parent = null)
	{
		// unify the URI
		$uri = trim($uri, '/');

		// and the namespace
		$namespace = trim($namespace, '\\');

		// and whether or not the component is routeable
		$routeable = (bool) $routeable;

		// resolve the parent if needed
		if (is_string($parent) and isset($this->_components[$parent]))
		{
			$parent = $this->_components[$parent];
		}

		// create the component instance, store it, and return it
		return $this->_components[$uri] = $this->factory->createComponentInstance($this, $uri, $namespace, $paths, $routeable, $parent);
	}

	/**
	 * Returns the applications environment string
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function environment()
	{
		return $this->_environment->getName();
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
	 * Returns the applications main component object
	 *
	 * @return  Component
	 *
	 * @since  2.0.0
	 */
	public function getComponent()
	{
		return $this->_component;
	}

	/**
	 * Returns all applications component objects
	 *
	 * @return  array of Component
	 *
	 * @since  2.0.0
	 */
	public function getComponents()
	{
		return $this->_components;
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
		return $this->getComponent()->getConfig();
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
		return $this->_environment;
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
		return $this->_event;
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
		return $this->getComponent()->getInput();
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
		return $this->_log;
	}

	/**
	 * Return the defined application name
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getName()
	{
		return $this->_name;
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
		return $this->_session;
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
		return $this->_viewManager;
	}

	/**
	 * Set the applications session manager
	 *
	 * @param  SessionManager   instance of Fuel\Session\Manager
	 *
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function setSession(SessionManager $session)
	{
		$this->_session = $session;
	}
}
