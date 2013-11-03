<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Providers;

use Fuel\Dependency\ServiceProvider;
use Fuel\Dependency\ResolveException;

/**
 * FuelPHP ServiceProvider class for this package
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class FuelServiceProvider extends ServiceProvider
{
	/**
	 * @var  array  list of service names provided by this provider
	 */
	public $provides = array(
		'application', 'environment', 'input', 'log',
		'request', 'request.local',
		'response', 'response.html', 'response.json', 'response.jsonp', 'response.csv', 'response.xml', 'response.redirect',
	);

	/**
	 * Service provider definitions
	 */
	public function provide()
	{
		/**
		 * Define the generic extensions provided by this service provider
		 */
		$this->extension('getApplicationInstance', function($container, $instance)
		{
			$stack = $this->container->resolve('requeststack');
			if ($request = $stack->top())
			{
				$app = $request->getApplication();
			}
			else
			{
				$app = $this->container->resolve('application.main');
			}

			if (is_callable(array($instance, 'setApplication')))
			{
				$instance->setApplication($app);
			}
			else
			{
				$instance->app = $app;
			}
		});

		$this->extension('getRequestInstance', function($container, $instance)
		{
			$stack = $this->container->resolve('requeststack');
			if (is_callable(array($instance, 'setRequest')))
			{
				$instance->setRequest($stack->top());
			}
			else
			{
				$instance->request = $stack->top();
			}
		});

		$this->extension('getLogInstance', function($container, $instance)
		{
			$stack = $this->container->resolve('requeststack');
			if ($request = $stack->top())
			{
				$log = $request->getApplication()->getLog();
			}
			else
			{
				$log = $this->container->resolve('application.main')->getLog();
			}

			if (is_callable(array($instance, 'setLog')))
			{
				$instance->setLog($log);
			}
			else
			{
				$instance->log = $log;
			}
		});

		$this->extension('getConfigInstance', function($container, $instance)
		{
			$stack = $this->container->resolve('requeststack');
			if ($request = $stack->top())
			{
				$config = $request->getApplication()->getConfig();
			}
			else
			{
				try
				{
					$config = $this->container->resolve('application.main')->getConfig();
				}
				catch (ResolveException $e)
				{
					$config = $this->container->resolve('config.global');
				}
			}

			if ($config)
			{
				if (is_callable(array($instance, 'setConfig')))
				{
					$instance->setConfig($config);
				}
				else
				{
					$instance->config = $config;
				}
			}
		});

		$this->extension('getRouterInstance', function($container, $instance)
		{
			$stack = $this->container->resolve('requeststack');
			if ($request = $stack->top())
			{
				$router = $request->getApplication()->getRouter();
			}
			else
			{
				$router = $this->container->resolve('application.main')->getRouter();
			}

			if (is_callable(array($instance, 'setRouter')))
			{
				$instance->setRouter($router);
			}
			else
			{
				$instance->router = $router;
			}
		});

		$this->extension('getEnvironmentInstance', function($container, $instance)
		{
			$stack = $this->container->resolve('requeststack');
			if ($request = $stack->top())
			{
				$environment = $request->getApplication()->getEnvironment();
			}
			else
			{
				$environment = $this->container->resolve('application.main')->getEnvironment();
			}

			if (is_callable(array($instance, 'setEnvironment')))
			{
				$instance->setEnvironment($environment);
			}
			else
			{
				$instance->environment = $environment;
			}
		});

		$this->extension('newFormatInstance', function($container, $instance)
		{
			$instance->format = $container->resolve('format');
		});

		/**
		 * Register the resources provided by this service provider
		 */

		// \Fuel\Foundation\Input
		$this->register('input', function ($dic, array $inputVars = array())
		{
			// find the parent input container
			$stack = $this->container->resolve('requeststack');
			if ($request = $stack->top())
			{
				$parent = $request->getApplication()->getInput();
			}
			else
			{
				try
				{
					$parent = $this->container->resolve('application.main')->getInput();
					if ( ! $parent)
					{
						$parent = $this->container->resolve('input.global');
					}
				}
				catch (ResolveException $e)
				{
					$parent = null;
				}
			}

			return $dic->resolve('Fuel\Foundation\Input', array($inputVars, $parent));
		});
		$this->extend('input', 'getConfigInstance');

		// \Fuel\Foundation\Application
		$this->register('application', function ($dic, $name, $path = null, $namespace = null, $environment = null)
		{
			// config was passed as an array, extract the data
			if (is_array($path))
			{
				// make sure the required fields exist
				$path = array_merge(array('path' => null, 'namespace' => '', 'environment' => ''), $path);

				// and extract them
				extract($path);
			}

			// application path
			if (empty($path))
			{
				$path = APPSPATH.$name;
			}
			$appPath = realpath($path);
			if ( ! is_dir($appPath))
			{
				throw new \InvalidArgumentException('FOU-004: The path ['.$path.'] does not exist for application ['.$name.'].');
			}

			// application namespace, defaults to global
			if (empty($namespace))
			{
				$namespace = '';
			}

			// application environment, defaults to 'development'
			if (empty($environment))
			{
				$environment = 'development';
			}

			// add the root namespace for this application to composer
			$dic->resolve('autoloader')->add($namespace, $appPath.DS.'classes', true);


			return $dic->resolve('Fuel\Foundation\Application', array($name, $appPath, $namespace, $environment));
		});

		// \Fuel\Foundation\Environment
		$this->register('environment', function ($dic, $environment)
		{
			// get current application and input objects
			$stack = $this->container->resolve('requeststack');
			if ($request = $stack->top())
			{
				$app = $request->getApplication();
			}
			else
			{
				$app = $this->container->resolve('application.main');
			}

			return $dic->resolve('Fuel\Foundation\Environment', array($environment, $app, $app->getInput(), $app->getConfig()));
		});

		$this->registerSingleton('requeststack', function ($dic)
		{
			return $dic->resolve('Fuel\Dependency\Stack');
		});

		// \Fuel\Foundation\Request\...
		$this->register('request', function ($dic, $resource, Array $input = array(), $type = null)
		{
			// get current application and input objects
			$stack = $this->container->resolve('requeststack');
			if ($request = $stack->top())
			{
				$app = $request->getApplication();
				$parentInput = $request->getInput();
			}
			else
			{
				$app = $this->container->resolve('application.main');
				$parentInput = $app->getInput();
			}

			// determine the type of request to return
			if ($type === null)
			{
				$url = parse_url($resource = rtrim($resource, '/').'/');

				// determine the type of request
				if (empty($resource) or empty($url['host']) or substr($resource,0,1) == '/')
				{
					// URI only, so it's an local request
					$resource  = '/'.trim(strval($resource), '/');
					$type = 'local';
				}
				else
				{
					// http request for this current base url?
					if (strpos($resource, $app->getEnvironment()->getBaseUrl()) === 0)
					{
						// request for the current base URL, so it's a local request too
						$resource  = empty($url['path']) ? '/' : $url['path'];
						$type = 'local';
					}
					else
					{
						// external URL, use the Curl request driver
						$type = 'curl';
					}
				}
			}
			elseif ( ! is_string($type) or empty($type))
			{
				// default to local
				$type = 'local';
			}

			// construct an input instance for this request
			$input = $dic->resolve('input', array($input, $parentInput));

			// return the constructed request
			return $dic->resolve('request.'.$type, array($app, $resource, $input));
		});

		// \Fuel\Foundation\Request\Local
		$this->register('request.local', function ($dic, $app, $resource = '', $inputInstance = null)
		{
			return $dic->resolve('Fuel\Foundation\Request\Local', array($app, $resource, $inputInstance));
		});
		$this->extend('request.local', 'getApplicationInstance');
		$this->extend('request.local', 'getRouterInstance');
		$this->extend('request.local', 'getLogInstance');

		// \Fuel\Foundation\Uri
		$this->register('uri', function ($dic, $uri)
		{
			return $dic->resolve('Fuel\Foundation\Uri', array($uri));
		});

		// \Fuel\Foundation\Response\Html
		$this->register('response', function ($dic, $type = 'html', $content = '', $status = 200, array $headers = array())
		{
			return $dic->resolve('Fuel\Foundation\Response\\'.ucfirst($type), array($content, $status, $headers));
		});
		$this->extend('response', 'getRequestInstance');

		// \Fuel\Foundation\Response\Html
		$this->register('response.html', function ($dic, $content = '', $status = 200, array $headers = array())
		{
			return $dic->resolve('Fuel\Foundation\Response\Html', array($content, $status, $headers));
		});
		$this->extend('response.html', 'getRequestInstance');

		// \Fuel\Foundation\Response\Json
		$this->register('response.json', function ($dic, $content = '', $status = 200, array $headers = array())
		{
			return $dic->resolve('Fuel\Foundation\Response\Json', array($content, $status, $headers));
		});
		$this->extend('response.json', 'getRequestInstance');
		$this->extend('response.json', 'newFormatInstance');

		// \Fuel\Foundation\Response\Jsonp
		$this->register('response.jsonp', function ($dic, $content = '', $status = 200, array $headers = array())
		{
			return $dic->resolve('Fuel\Foundation\Response\Jsonp', array($content, $status, $headers));
		});
		$this->extend('response.jsonp', 'getRequestInstance');
		$this->extend('response.jsonp', 'newFormatInstance');

		// \Fuel\Foundation\Response\Csv
		$this->register('response.csv', function ($dic, $content = '', $status = 200, array $headers = array())
		{
			return $dic->resolve('Fuel\Foundation\Response\Csv', array($content, $status, $headers));
		});
		$this->extend('response.csv', 'getRequestInstance');
		$this->extend('response.csv', 'newFormatInstance');

		// \Fuel\Foundation\Response\Xml
		$this->register('response.xml', function ($dic, $content = '', $status = 200, array $headers = array())
		{
			return $dic->resolve('Fuel\Foundation\Response\Xml', array($content, $status, $headers));
		});
		$this->extend('response.xml', 'getRequestInstance');
		$this->extend('response.xml', 'newFormatInstance');

		// \Fuel\Foundation\Response\Redirect
		$this->register('response.redirect', function ($dic, $url = '', $method = 'location', $status = 302, array $headers = array())
		{
			return $dic->resolve('Fuel\Foundation\Response\Redirect', array($url, $method, $status, $headers));
		});

		// \Fuel\Database\Connection
		$this->register('storage.db', function ($dic, $config = null)
		{
			// get the correct config instance
			$stack = $this->container->resolve('requeststack');
			if ($request = $stack->top())
			{
				$app = $request->getApplication();
			}
			else
			{
				$app = $this->container->resolve('application.main');
			}

			// load the db config
			$app->getConfig()->load('db', true);

			// construct the config array
			if ( ! is_array($config) or empty($config))
			{
				// if we don't have a config requested, get the configured active config
				if (empty($config))
				{
					$config = $app->getConfig()->get('active', 'default');
				}
				$name = $config;

				$config = \Arr::merge($this->storage_db_defaults, $app->getConfig()->get('db.'.$config, array()));
			}
			else
			{
				$name = uniqid();
				$config = \Arr::merge($this->storage_db_defaults, $config);
			}

			// default to mysql if we don't have a driver set
			if ( ! isset($config['driver']))
			{
				$config['driver'] = 'mysql';
			}

			return $dic->multiton('Fuel\Database\Connection\\'.ucfirst($config['driver']), $name, array($config));
		});

		// \Memcached
		$this->register('storage.memcached', function ($dic, $config = null)
		{
			// do we have the PHP memcached extension available
			if ( ! class_exists('Memcached') )
			{
				throw new \InvalidArgumentException('FOU-029: your PHP installation doesn\'t have the Memcached PECL extension loaded.');
			}

			// get the correct config instance
			$stack = $this->container->resolve('requeststack');
			if ($request = $stack->top())
			{
				$app = $request->getApplication();
			}
			else
			{
				$app = $this->container->resolve('application.main');
			}

			// load the db config
			$app->getConfig()->load('memcached', true);

			// construct the config array
			if ( ! is_array($config) or empty($config))
			{
				// if we don't have a config requested, get the configured active config
				if (empty($config))
				{
					$config = $app->getConfig()->get('active', 'default');
				}
				$name = $config;

				$config = $app->getConfig()->get('memcached.'.$config, array());
			}
			else
			{
				$name = uniqid();
			}

			// check if we have a persistent_id defined
			$persistent_id = isset($config['persistent_id']) ? $config['persistent_id'] : null;

			// fetch the instance
			$instance = $dic->multiton('Memcached', $name, array($persistent_id, null));

			// new instance? then configure it
			$servers = $instance->getServerList();
			if (empty($servers))
			{
				if (isset($config['servers']))
				{
					$instance->addServers($config['servers']);
				}
				if (isset($config['options']))
				{
					$instance->setOptions($config['options']);
				}
			}

			// check if we have a connection to at least one memcached server
			$servers = $instance->getVersion();
			if (is_array($servers))
			{
				// filter out dead servers
				$servers = array_filter($servers, function($var) { return $var !== '255.255.255'; });
			}

			if (empty($servers))
			{
				throw new \RuntimeException('FOU-030: There is no connection possible to memcached server(s) identified by ['.$name.']. Check your configuration.');
			}

			// return the instance
			return $instance;
		});

		// \Fuel\Foundation\Session\Db
		$this->register('session.db', function ($dic, Array $config = array())
		{
die('NOT IMPLEMENTED!');
		});

		// \Fuel\Foundation\Session\Memcached
		$this->register('session.memcached', function ($dic, Array $config = array())
		{
			$name = empty($config['memcached']['name']) ? null : $config['memcached']['name'];
			return $dic->resolve('Fuel\Foundation\Session\Memcached', array($config, $dic->resolve('storage.memcached', array($name))));
		});

		/**
		 * Service definitions for required non-Fuel classes
		 */

		// \Monolog\Logger
		$this->register('log', function ($dic, $name, array $handlers = array(), array $processors = array())
		{
			return new \Monolog\Logger($name, $handlers, $processors);
		});
	}
}
