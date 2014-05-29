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

/**
 * Component class
 *
 * Defines a FuelPHP application component
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Component
{
	/**
	 * @var  InjectionFactory  this applications object factory
	 *
	 * @since  2.0.0
	 */
	protected $factory;

	/**
	 * @var  string  application
	 *
	 * @since  2.0.0
	 */
	protected $app;

	/**
	 * @var  string  Base URI for this component
	 */
	protected $uri;

	/**
	 * @var  string  Base Namespace for this component
	 */
	protected $namespace;

	/**
	 * @var  array  Paths to the component root
	 */
	protected $paths = array();

	/**
	 * @var  boolean  Whether or not this component is routeable
	 */
	protected $routeable = true;

	/**
	 * @var  Application|Component  Parent of this Component object
	 */
	protected $parent;

	/**
	 * @var  array  List of children of this Component
	 */
	protected $children = array();

	/**
	 * @var  Fuel/Config/Container  this components config object
	 *
	 * @since  2.0.0
	 */
	protected $config;

	/**
	 * @var  Input  this components input object
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
	 * Constructor
	 *
	 * @param  string                     $uri         base URI for this component
	 * @param  string                     $namespace   this applications base namespace
	 * @param  array                      $paths       path to the application component
	 * @param  Component                  $parent      parent Component instance
	 * @param  Fuel\Config\Datacontainer  $config      Config Container instance
	 * @param  Input                      $input       Input Container instance
	 * @param  Fuel\Routing\Router        $router      Routing engine instance
	 * @param  Composer\ClassLoader       $autoloader  Autoloader instance
	 * @param  InjectionFactory           $factory     factory object to construct external objects
	 *
	 * @since  2.0.0
	 */
	public function __construct($app, $uri, $namespace, $paths, $routeable, $parent, $config, $input, $router, $autoloader, InjectionFactory $factory)
	{
		// store the component object factory
		$this->factory = $factory;

		// store the app
		$this->app = $app;

		// unify the URI
		$this->uri = trim($uri, '/');

		// and the namespace
		$this->namespace = trim($namespace, '\\');

		// and the routeable flag
		$this->routeable = (bool) $routeable;

		// TODO: needs a better solution than hardcoding paths!
		// process and verify the paths
		foreach ($paths = (array) $paths as $path)
		{
			// get what we think is the root path
			$path = realpath($path.DS.'..'.DS);

			// check if this is a valid component
			if (is_dir($path.DS.'classes') and ! in_array($path, $this->paths))
			{
				// store it
				$this->paths[] = $path;

				// and add it to the autoloader if needed
				$prefixes = $autoloader->getPrefixesPsr4();
				if ( ! isset($prefixes[$this->namespace.'\\']) or ! in_array($path.DS.'classes', $prefixes[$this->namespace.'\\']))
				{
					$autoloader->addPsr4($this->namespace.'\\', $path.DS.'classes');
				}
			}
		}

		// if the parent is a Component too
		if ($parent instanceOf Component)
		{
			// make a backlink to link parent and child
			$parent->setChild($this);

			// link to our parents router
			$this->router = $parent->getRouter();
		}
		// otherwise we're the main application component, do some initalisation
		else
		{
			// setup the global config defaults
			$config->addPath(realpath(__DIR__.DS.'..'.DS.'defaults').DS);

			// import global data
			$input->fromGlobals();

			// assign the configuration container to this input instance
			$input->setConfig($config);

			// store the router instance
			$this->router = $router;

			// add our route filter to be able to resolve controllers
			$this->router->setAutoFilter(array($this->factory->getRouteFilter($this), 'filter'));
		}

		// add the paths to the config
		foreach ($this->paths as $path)
		{
			$config->addPath($path.DS);
		}

		// store the config container
		$this->config = $config;

		// load the application configuration
		$this->config->load('config', null);

		// load the file config
		$this->config->load('file', true);

		// load the lang config
		$this->config->load('lang', true);

		// store our parent object
		$this->parent = $parent ?: $app;

		// and load any defined routes in this module
		$this->loadRoutes();

		// store the input instance
		$this->input = $input;

		// and finally run the component bootstrap if present
		$this->runBootstrap();
	}

	/**
	 * Returns the application object
	 *
	 * @return  Application
	 *
	 * @since  2.0.0
	 */
	public function getApplication()
	{
		return $this->app;
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
	 * Returns the applications input object
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
	 * Return the base URI for this component
	 *
	 * @return  string  base URI
	 *
	 * @since  2.0.0
	 */
	public function getUri()
	{
		return $this->uri;
	}

	/**
	 * Return the base namespace for this component
	 *
	 * @return  string  base Namespace
	 *
	 * @since  2.0.0
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * Return this Component objects parent
	 *
	 * @return  Application|Component  the parent object
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Return the primary component root path
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getPath()
	{
		return reset($this->paths);
	}

	/**
	 * Return the component root paths
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getPaths()
	{
		return $this->paths;
	}

	/**
	 * Return the component routing engine
	 *
	 * @return  Fuel\Routing\Router
	 *
	 * @since  2.0.0
	 */
	public function getRouter()
	{
		return $this->router;
	}

	/**
	 * Sets a child of this Component instance
	 */
	public function setChild($component)
	{
		if ($component instanceOf Component)
		{
			$this->children[] = $component;
		}
	}

	/**
	 * Returns whether or not this component is routeable
	 *
	 * @return  boolean
	 *
	 * @since  2.0.0
	 */
	public function isRoutable()
	{
		return $this->routeable;
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
		// we construct components in Application, so bubble up until we get there
		return $this->parent->newComponent($uri, $namespace, $paths, $routeable, $parent ?: $this);
	}

	/**
	 * Construct an component request
	 *
	 * @param   string  $uri
	 * @param   array   $input
	 *
	 * @return  Request
	 *
	 * @since  2.0.0
	 */
	public function getRequest($uri = null, $input = array())
	{
		// if no uri is given, fetch the global one
		if ($uri === null)
		{
			$uri = $this->input->getPathInfo();
		}

		// log the request
		$this->app->getLog()->info('Application "'.$this->app->getName().'" is creating new "'.$this->input->getMethod().'" Request for URI: '.(empty($uri) ? '/' : $uri));

		// forge a new request
		return $this->factory->createRequestInstance($this, $uri, $input);
	}

	/**
	 * Return a language container instance
	 *
	 * @param  string   language to fetch, or null for the current active language
	 *
	 * @return  Fuel\Config\Container
	 *
	 * @since  2.0.0
	 */
	public function getLanguage($language = null)
	{
		if ($language === null)
		{
			$language = $this->getConfig()->get('lang.current', $this->getConfig()->get('lang.fallback', 'en'));
		}

		if ( ! isset($this->_languages[$language]))
		{
			$this->_languages[$language] = $this->factory->createLanguageInstance($this->_name.'-'.$language);
			$this->_languages[$language]
				->addPath(realpath(__DIR__.DS.'..'.DS.'defaults'.DS.'lang'.DS.$language).DS)
				->addPath($this->appPath.'lang'.DS.$language.DS);
		}

		return $this->_languages[$language];
	}

	/**
	 * Set an applications language container instance
	 *
	 * @param  string            language to set
	 * @param  ConfigContainer   instance of Fuel\Config\Container
	 *
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function setLanguage($language, ConfigContainer $instance)
	{
		$this->_languages[$language] = $instance;
	}

	/**
	 * Import routes from the component paths config folder, if defined
	 */
	protected function loadRoutes()
	{
		// closure to import routes in their own scope
		$loadroutes = function($__file__) {
			return include $__file__;
		};

		foreach ($this->paths as $path)
		{
			if (file_exists($path = $path.DS.'config'.DS.'routes.php'))
			{
				$routes = $loadroutes($path);

				if (is_array($routes))
				{
					// TODO, process v1.x type route array
				}
			}
		}
	}

	/**
	 * Check if the created component has a bootstrap file, and if so, execute it
	 */
	protected function runBootstrap()
	{
		// closure to run the bootstap in its own scope
		$runbootstrap = function($__file__) {
			return include $__file__;
		};

		foreach ($this->paths as $path)
		{
			if (file_exists($path = $path.DS.'bootstrap.php'))
			{
				$runbootstrap($path);
			}
		}
	}
}
