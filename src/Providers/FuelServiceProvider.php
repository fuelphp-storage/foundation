<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Providers;

use Fuel\Dependency\ServiceProvider;
use Fuel\Dependency\ResolveException;
use Fuel\Foundation\RouteFilter;

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
		'environment', 'component', 'requeststack', 'input',
		'session.db', 'session.memcached', 'session.redis',
		'router', 'log',

		'request', 'request.local',
		'response', 'response.html', 'response.json', 'response.jsonp', 'response.csv', 'response.xml', 'response.redirect',
		'storage.db', 'storage.memcached', 'storage.redis',
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
			try
			{
				$stack = $container->resolve('requeststack');
				if ($request = $stack->top())
				{
					$app = $request->getComponent()->getApplication();
				}
				else
				{
					$app = $container->resolve('application::__main');
				}

				if (is_callable(array($instance, 'setApplication')))
				{
					$instance->setApplication($app);
				}
				else
				{
					$instance->app = $app;
				}
			}
			catch (\Fuel\Dependency\ResolveException $e)
			{
				// ignore
			}
		});

		$this->extension('getConfigInstance', function($container, $instance)
		{
			try
			{
				$stack = $container->resolve('requeststack');
				if ($request = $stack->top())
				{
					$config = $request->getComponent()->getConfig();
				}
				else
				{
					$config = $container->resolve('application::__main')->getRootComponent()->getConfig();
				}

				if (is_callable(array($instance, 'setConfig')))
				{
					$instance->setConfig($config);
				}
				else
				{
					$instance->config = $config;
				}
			}
			catch (\Fuel\Dependency\ResolveException $e)
			{
				// ignore
			}
		});

		$this->extension('getInputInstance', function($container, $instance)
		{
			try
			{
				$stack = $container->resolve('requeststack');
				if ($request = $stack->top())
				{
					$input = $request->getComponent()->getInput();
				}
				else
				{
					$input = $container->resolve('application::__main')->getRootComponent()->getInput();
				}

				if (is_callable(array($instance, 'setInput')))
				{
					$instance->setInput($input);
				}
				else
				{
					$instance->input = $input;
				}
			}
			catch (\Fuel\Dependency\ResolveException $e)
			{
				// ignore
			}
		});

		$this->extension('getLogInstance', function($container, $instance)
		{
			try
			{
				$stack = $container->resolve('requeststack');
				if ($request = $stack->top())
				{
					$log = $request->getComponent()->getApplication()->getLog();
				}
				else
				{
					$log = $container->resolve('application::__main')->getLog();
				}

				if (is_callable(array($instance, 'setLog')))
				{
					$instance->setLog($log);
				}
				else
				{
					$instance->log = $log;
				}
			}
			catch (\Fuel\Dependency\ResolveException $e)
			{
				// ignore
			}
		});

		$this->extension('getRouterInstance', function($container, $instance)
		{
			try
			{
				$stack = $container->resolve('requeststack');
				if ($request = $stack->top())
				{
					$router = $request->getComponent()->getRouter();
				}
				else
				{
					$router = $container->resolve('application::__main')->getRootComponent()->getRouter();
				}

				if (is_callable(array($instance, 'setRouter')))
				{
					$instance->setRouter($router);
				}
				else
				{
					$instance->router = $router;
				}
			}
			catch (\Fuel\Dependency\ResolveException $e)
			{
				// ignore
			}
		});

		$this->extension('getEnvironmentInstance', function($container, $instance)
		{
			try
			{
				$stack = $container->resolve('requeststack');
				if ($request = $stack->top())
				{
					$environment = $request->getComponent()->getApplication()->getEnvironment();
				}
				else
				{
					$environment = $container->resolve('application::__main')->getEnvironment();
				}

				if (is_callable(array($instance, 'setEnvironment')))
				{
					$instance->setEnvironment($environment);
				}
				else
				{
					$instance->environment = $environment;
				}
			}
			catch (\Fuel\Dependency\ResolveException $e)
			{
				// ignore
			}
		});

		$this->extension('getRequestInstance', function($container, $instance)
		{
			$stack = $container->resolve('requeststack');
			if (is_callable(array($instance, 'setRequest')))
			{
				$instance->setRequest($stack->top());
			}
			else
			{
				$instance->request = $stack->top();
			}
		});

		$this->extension('getAutoloaderInstance', function($container, $instance)
		{
			$autoloader = $container->resolve('autoloader');
			if (is_callable(array($instance, 'setAutoloader')))
			{
				$instance->setAutoloader($autoloader);
			}
			else
			{
				$instance->autoloader = $autoloader;
			}
		});





		/*
		 * Instance definitions
		 */

		// \Fuel\Foundation\Environment
		$this->register('environment', function ($dic, $environment, $app)
		{
			return $dic->resolve('Fuel\Foundation\Environment', array($environment, $app));
		});

		$this->registerSingleton('requeststack', function ($dic)
		{
			return $dic->resolve('Fuel\Foundation\Stack');
		});

		// \Fuel\Foundation\Component
		$this->register('component', function ($dic, $app, $uri, $namespace, $paths, $routeable, $parent)
		{
			// make sure paths is an array
			$paths = (array) $paths;

			// get the autoloader instance
			$loader = $dic->resolve('autoloader');

			// get all defined namespaces
			$prefixes = array_merge($loader->getPrefixes(), $loader->getPrefixesPsr4());

			// check if we have a definition for this namespace
			if (isset($prefixes[$namespace]))
			{
				$paths = array_merge($prefixes[$namespace], $paths);
			}
			elseif (isset($prefixes[$namespace .= '\\']))
			{
				$paths = array_merge($prefixes[$namespace], $paths);
			}

			if (empty($paths))
			{
				throw new \InvalidArgumentException('FOU-xxx: Location of component identified by namespace ['.trim($namespace, '\\').'] can not be determined. Can not create a component instance.');
			}

			// create the component instance for this namespace
			$config = $dic->multiton('config', trim($namespace, '\\'));
			$input = $dic->resolve('input');
			$input->setConfig($config);
			if ($parent)
			{
				$config->setParent($parent->getConfig());
				$input->setParent($parent->getInput());
			}

			return $dic->multiton('Fuel\Foundation\Component', $uri, array($app, $uri, $namespace, $paths, $routeable, $parent, $config, $input, $dic->resolve('autoloader')));
		});

		// \Fuel\Foundation\Input
		$this->register('input', function ($dic, $inputVars = array(), $parent = null)
		{
			return $dic->resolve('Fuel\Foundation\Input', array($inputVars, $parent));
		});
		$this->extend('input', 'getConfigInstance');

		// \Fuel\Foundation\Session\Db
		$this->register('session.db', function ($dic, Array $config = array())
		{
			$name = empty($config['db']['name']) ? null : $config['db']['name'];
			return $dic->resolve('Fuel\Foundation\Session\Db', array($config, $dic->resolve('storage.db', array($name))));
		});

		// \Fuel\Foundation\Session\Memcached
		$this->register('session.memcached', function ($dic, Array $config = array())
		{
			$name = empty($config['memcached']['name']) ? null : $config['memcached']['name'];
			return $dic->resolve('Fuel\Foundation\Session\Memcached', array($config, $dic->resolve('storage.memcached', array($name))));
		});

		// \Fuel\Foundation\Session\Redis
		$this->register('session.redis', function ($dic, Array $config = array())
		{
			$name = empty($config['redis']['name']) ? null : $config['redis']['name'];
			return $dic->resolve('Fuel\Foundation\Session\Redis', array($config, $dic->resolve('storage.redis', array($name))));
		});

		// \Fuel\Foundation\Request\...
		$this->register('request', function ($dic, $component, $resource, Array $input = array(), $type = null)
		{
			// get the parent input objects
			$parentInput = $component->getInput();

			// determine the type of request to return
			if ($type === null)
			{
				$url = parse_url($resource = rtrim($resource, '/').'/');

				// determine the type of request
				if ((bool) defined('STDIN'))
				{
					// task request
					$type = 'cli';
				}
				elseif (empty($resource) or empty($url['host']) or substr($resource,0,1) == '/')
				{
					// URI only, so it's an local request
					$resource  = '/'.trim(strval($resource), '/');
					$type = 'local';
				}
				else
				{
					// http request for this current base url?
					if (strpos($resource, $component->getApplication()->getEnvironment()->getBaseUrl()) === 0)
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
			return $dic->resolve('request.'.$type, array($component, $resource, $input));
		});

		// \Fuel\Foundation\Request\Local
		$this->register('request.local', function ($dic, $component, $resource = '', $inputInstance = null)
		{
			return $dic->resolve('Fuel\Foundation\Request\Local', array($component, $resource, $inputInstance));
		});

		// \Fuel\Foundation\Request\Cli
		$this->register('request.cli', function ($dic, $component, $resource = '', $inputInstance = null)
		{
			return $dic->resolve('Fuel\Foundation\Request\Cli', array($component, $resource, $inputInstance));
		});

		// \Fuel\Foundation\Router
		$this->register('router', function ($dic, $component)
		{
			return $dic->resolve('Fuel\Foundation\Router', array($component));
		});

		/**
		 * Service definitions for required non-Fuel classes
		 */

		// \Monolog\Logger
		$this->register('log', function ($dic, $name, array $handlers = array(), array $processors = array())
		{
			return new \Monolog\Logger($name, $handlers, $processors);
		});





























		$this->extension('newFormatInstance', function($container, $instance)
		{
			$instance->format = $container->resolve('format');
		});

		/**
		 * Register the resources provided by this service provider
		 */


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
			// get the correct config instance
			$stack = $dic->resolve('requeststack');
			if ($request = $stack->top())
			{
				$app = $request->getComponent()->getApplication();
			}
			else
			{
				$app = $dic->resolve('application::__main');
			}

			return $dic->resolve('Fuel\Foundation\Response\Redirect', array($app, $url, $method, $status, $headers));
		});
		$this->extend('response.redirect', 'getRequestInstance');

		// \Fuel\Database\Connection
		$this->register('storage.db', function ($dic, $config = null)
		{
			// get the correct config instance
			$stack = $dic->resolve('requeststack');
			if ($request = $stack->top())
			{
				$app = $request->getApplication();
			}
			else
			{
				$app = $dic->resolve('application.main');
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

				$config = $app->getConfig()->get('db.'.$config, array());
			}
			else
			{
				$name = uniqid();
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
			if ( ! class_exists('Memcached'))
			{
				throw new \InvalidArgumentException('FOU-029: your PHP installation doesn\'t have the Memcached PECL extension loaded.');
			}

			// get the correct config instance
			$stack = $dic->resolve('requeststack');
			if ($request = $stack->top())
			{
				$app = $request->getApplication();
			}
			else
			{
				$app = $dic->resolve('application.main');
			}

			// load the memcached config
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
				throw new \RuntimeException('FOU-030: There is no connection possible to the memcached server(s) identified by ['.$name.']. Check your configuration.');
			}

			// return the instance
			return $instance;
		});

		// \Redis
		$this->register('storage.redis', function ($dic, $config = null)
		{
			// get the correct config instance
			$stack = $dic->resolve('requeststack');
			if ($request = $stack->top())
			{
				$app = $request->getApplication();
			}
			else
			{
				$app = $dic->resolve('application.main');
			}

			// load the redis config
			$app->getConfig()->load('redis', true);

			// construct the config array
			if ( ! is_array($config) or empty($config))
			{
				// if we don't have a config requested, get the configured active config
				if (empty($config))
				{
					$config = $app->getConfig()->get('active', 'default');
				}
				$name = $config;

				$config = $app->getConfig()->get('redis.'.$config, array());
			}
			else
			{
				$name = uniqid();
			}

			// check if we have a class configured, default to PECL
			$class = empty($config['class']) ? 'Redis' : ucfirst($config['class']);

			// get us an instance
			switch ($class)
			{
				// Redis PECL extension, or an emulation
				case 'Redis':

					// fetch the instance
					$instance = $dic->multiton('Redis', $name);

					try
					{
						// already connected?
						$instance->ping();
					}
					catch (\RedisException $e)
					{
						// get the first defined server
						if (isset($config['servers']) and is_array($config['servers']))
						{
							$server = (array) reset($config['servers']);
						}
						else
						{
							$server = array();
						}

						// validate some config
						if ( ! isset($server['timeout']) or ! is_numeric($server['timeout']))
						{
							$server['timeout'] = 0;
						}
						if ( ! isset($server['port']))
						{
							$server['port'] = null;
						}

						// new connection, connect and configure
						if ( ! $instance->connect($server['host'], $server['port'], $server['timeout']))
						{
							throw new \RuntimeException('FOU-032: Can not connect to your Redis server.');
						}

						// authenticate if needed
						if ( ! empty($server['auth']))
						{
							$instance->auth($server['auth']);
						}

						// switch to the correct database
						if (isset($server['database']) and is_numeric($server['database']))
						{
							$instance->select($server['database']);
						}

						// and configure additional connection options
						if ( ! empty($config['options']) and is_array($config['options']))
						{
							foreach ($config['options'] as $key => $value)
							{
								$instance->setOption($key, $value);
							}
						}
					}

				break;

				// Predis composer package
				case 'Predis':

					if ( ! class_exists('Predis\Client'))
					{
						throw new \RuntimeException('FOU-031: Your installation doesn\'t have the Predis package available.');
					}

					// prep the connection parameters
					if ( ! isset($config['servers']) or ! is_array($config['servers']))
					{
						$config['servers'] = null;
						$cluster = false;
					}
					else
					{
						if (count($config['servers']) == 1)
						{
							$cluster = false;
							$config['servers'] = reset($config['servers']);
						}
						else
						{
							$cluster = true;
						}
					}
					if ( ! isset($config['options']) or ! is_array($config['options']))
					{
						$config['options'] = null;
					}

					// fetch the instance
					$instance = $dic->multiton('Predis\Client', $name, array($config['servers'], $config['options']));

				break;

				// Redisent Composer package
				case 'Redisent':

					if ( ! class_exists('redisent\Redis'))
					{
						throw new \RuntimeException('FOU-034: Your installation doesn\'t have the Redisent package available.');
					}

					// get the first defined server
					if (isset($config['servers']) and is_array($config['servers']))
					{
						$server = (array) reset($config['servers']);
					}
					else
					{
						$server = array('host' => 'localhost', 'port' => 6379);
					}

					// prefix with the correct scheme if needed
					if (strpos($server['host'], '://') === false)
					{
						$server['host'] = 'redis://'.$server['host'];
					}

					// in case of a unix socket, we don't need a port
					if (strpos($server['host'], 'unix://') === false)
					{
						$server['host'] .= empty($server['port']) ? ':6379' : (':'.$server['port']);
					}

					// validate some config
					if ( ! isset($server['timeout']) or ! is_numeric($server['timeout']))
					{
						$server['timeout'] = null;
					}

					// fetch the instance
					$instance = $dic->multiton('redisent\Redis', $name, array($server['host'], $server['timeout']));

					// authenticate if needed
					if ( ! empty($server['auth']))
					{
						$instance->auth($server['auth']);
					}

					// switch to the correct database
					if (isset($server['database']) and is_numeric($server['database']))
					{
						$instance->select($server['database']);
					}

				break;

				// unsupported class, bail out!
				default:
					throw new \InvalidArgumentException('FOU-033: "['.$class.']" is not a supported Redis class.');

			}

			// return the instance
			return $instance;
		});
	}
}
