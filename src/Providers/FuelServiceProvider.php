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

use League\Container\ServiceProvider;
use League\Container\Exception\ReflectionException;
use Fuel\Foundation;
use Monolog\Logger;

/**
 * Fuel ServiceProvider class for Foundation
 */
class FuelServiceProvider extends ServiceProvider
{
	/**
	 * @var array
	 */
	protected $provides = [
		'applicationInstance', 'componentInstance', 'configInstance',
		'langInstance', 'inputInstance', 'logInstance', 'routerInstance',
		'environmentInstance', 'requestInstance',

		'application', 'environment', 'component', 'requeststack', 'injectionfactory', 'input',
		'session.db', 'session.memcached', 'session.redis',
		'router', 'log', 'event',

		'request', 'request.local',
		'response', 'response.html', 'response.json', 'response.jsonp', 'response.csv', 'response.xml', 'response.redirect',
		'storage.db', 'storage.memcached', 'storage.redis',
	];

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->add('applicationInstance', function($ignoreException = true)
		{
			try
			{
				$stack = $this->container->get('requeststack');

				if ($request = $stack->top())
				{
					return $request->getComponent()->getApplication();
				}

				return $this->container->get('application::__main');
			}
			catch (ReflectionException $e)
			{
				if ( ! $ignoreException)
				{
					throw $e;
				}
			}
		});

		$this->container->add('componentInstance', function($ignoreException = true)
		{
			try
			{
				$stack = $this->container->get('requeststack');

				if ($request = $stack->top())
				{
					return $request->getComponent();
				}

				return $app = $this->container->get('application::__main')->getRootComponent();
			}
			catch (ReflectionException $e)
			{
				if ( ! $ignoreException)
				{
					throw $e;
				}
			}
		});

		$this->container->add('configInstance', function()
		{
			if ($component = $this->container->get('componentInstance'))
			{
				return $component->getConfig();
			}
		});

		$this->container->add('langInstance', function()
		{
			if ($component = $this->container->get('componentInstance'))
			{
				return $component->getLanguage();
			}
		});

		$this->container->add('inputInstance', function()
		{
			if ($component = $this->container->get('componentInstance'))
			{
				return $component->getInput();
			}
		});

		$this->container->add('logInstance', function()
		{
			if ($app = $this->container->get('applicationInstance'))
			{
				return $app->getLog();
			}
		});

		$this->container->add('routerInstance', function()
		{
			if ($component = $this->container->get('componentInstance'))
			{
				return $component->getRouter();
			}
		});

		$this->container->add('environmentInstance', function()
		{
			if ($app = $this->container->get('applicationInstance'))
			{
				return $app->getEnvironment();
			}
		});

		$this->container->add('requestInstance', function()
		{
			$stack = $this->container->get('requeststack');

			return $stack->top();
		});


		$this->container->add('application', function($name, $appNamespace, $appEnvironment)
		{
			$injectionFactory = $this->container->get('injectionfactory');

			return new Foundation\Application($name, $appNamespace, $appEnvironment, $injectionFactory);
		});


		$this->container->add('environment', function ($environment, $app)
		{
			return new Foundation\Environment($environment, $app);
		});

		$this->container->singleton('requeststack', 'Fuel\Foundation\Stack')
			->withArgument('Fuel\Dependency\Container');

		$this->container->singleton('injectionfactory', 'Fuel\Foundation\InjectionFactory')
			->withArgument('Fuel\Dependency\Container');

		$this->container->add('component', function ($app, $uri, $namespace, $paths, $routeable, $parent)
		{
			// make sure paths is an array
			$paths = (array) $paths;

			// get the autoloader instance
			$loader = $this->container->get('autoloader');

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
			$config = $this->container->multiton('config', trim($namespace, '\\'));
			$input = $this->container->get('input');
			$input->setConfig($config);

			if ($parent)
			{
				$config->setParent($parent->getConfig());
				$input->setParent($parent->getInput());
			}

			$autoloader = $this->container->get('autoloader');
			$injectionFactory = $this->container->get('injectionfactory');

			return new Foundation\Component($app, $uri, $namespace, $paths, $routeable, $parent, $config, $input, $autoloader, $injectionFactory);
		});

		$this->container->add('input', function ($inputVars = [], $parent = null)
		{
			$injectionFactory = $this->container->get('injectionfactory');

			$input = new Foundation\Input($inputVars, $parent, $injectionFactory);

			// $input->setConfig($this->container->get('configInstance'));

			return $input;
		});

		$this->container->add('session.db', function (array $config = [])
		{
			$name = empty($config['db']['name']) ? null : $config['db']['name'];
			$storage = $this->container->get('storage.db', [$name]);

			return new Foundation\Session\Db($config, $storage);
		});

		$this->container->add('session.memcached', function (array $config = [])
		{
			$name = empty($config['memcached']['name']) ? null : $config['memcached']['name'];
			$storage = $this->container->get('storage.memcached', [$name]);

			return new Foundation\Session\Memcached($config, $storage);
		});

		$this->container->add('session.redis', function (array $config = [])
		{
			$name = empty($config['redis']['name']) ? null : $config['redis']['name'];
			$storage = $this->container->get('storage.redis', [$name]);

			return new Foundation\Session\Redis($config, $storage);
		});

		$this->container->singleton('requestinjectionfactory', 'Fuel\Foundation\Request\RequestInjectionFactory')
			->withArgument('Fuel\Dependency\Container');

		$this->container->add('request', function ($component, $resource, array $input = [], $type = null)
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
			$input = $this->container->get('input', [$input, $parentInput]);

			// return the constructed request
			return $this->container->get('request.'.$type, [$component, $resource, $input]);
		});

		$this->container->inflector('Fuel\Foundation\Request\RequestAware')
			->invokeMethod('setRequest', ['requestInstance']);

		$this->container->add('request.local', function ($component, $resource = '', $inputInstance = null)
		{
			$injectionFactory = $this->container->get('requestinjectionfactory');

			return new Foundation\Request\Local($component, $resource, $inputInstance, $injectionFactory);
		});

		$this->container->add('request.cli', function ($component, $resource = '', $inputInstance = null)
		{
			$injectionFactory = $this->container->get('requestinjectionfactory');

			return new Foundation\Request\Cli($component, $resource, $inputInstance, $injectionFactory);
		});

		$this->container->add('router', function ($component)
		{
			$injectionFactory = $this->container->get('injectionfactory');

			return new Foundation\Router($component, $injectionFactory);
		});



		$this->container->add('log', function ($name, array $handlers = [], array $processors = [])
		{
			return new Logger($name, $handlers, $processors);
		});

		$this->container->add('event', 'League\Event\Emitter');




		$this->container->add('uri', function ($uri)
		{
			return new Foundation\Uri($uri);
		});

		$this->container->add('response', function ($type = 'html', $content = '', $status = 200, array $headers = [])
		{
			return $this->container->get('response.'.$type, [$content, $status, $headers]);
		});

		$this->container->inflector('Fuel\Foundation\Response\FormatAware')
			->invokeMethod('setFormat', ['format']);

		$this->container->add('response.html', function ($content = '', $status = 200, array $headers = [])
		{
			return new Foundation\Response\Html($content, $status, $headers);
		});

		$this->container->add('response.json', function ($content = '', $status = 200, array $headers = [])
		{
			return new Foundation\Response\Json($content, $status, $headers);
		});

		$this->container->add('response.jsonp', function ($content = '', $status = 200, array $headers = [])
		{
			return new Foundation\Response\Jsonp($content, $status, $headers);
		});

		$this->container->add('response.csv', function ($content = '', $status = 200, array $headers = [])
		{
			return new Foundation\Response\Csv($content, $status, $headers);
		});

		$this->container->add('response.xml', function ($content = '', $status = 200, array $headers = [])
		{
			return new Foundation\Response\Xml($content, $status, $headers);
		});

		$this->container->add('response.redirect', function ($url = '', $method = 'location', $status = 302, array $headers = [])
		{
			$app = $this->container->get('applicationInstance', [false]);

			return new Foundation\Response\Redirect($app, $url, $method, $status, $headers);
		});

		// \Fuel\Database\Connection
		$this->container->add('storage.db', function ($config = null)
		{
			$app = $this->container->get('applicationInstance', [false]);

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

				$config = $app->getConfig()->get('db.'.$config, []);
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

			return $this->container->multiton('storage.'.$config['driver'], $name, [$config]);
		});

		$this->container->add('storage.memcached', function ($config = null)
		{
			// do we have the PHP memcached extension available
			if ( ! class_exists('Memcached'))
			{
				throw new \InvalidArgumentException('FOU-029: your PHP installation doesn\'t have the Memcached PECL extension loaded.');
			}

			$app = $this->container->get('applicationInstance', [false]);

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

				$config = $app->getConfig()->get('memcached.'.$config, []);
			}
			else
			{
				$name = uniqid();
			}

			// check if we have a persistent_id defined
			$persistent_id = isset($config['persistent_id']) ? $config['persistent_id'] : null;

			// fetch the instance
			$instance = $this->container->multiton('Memcached', $name, [$persistent_id, null]);

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

		$this->container->add('storage.redis', function ($config = null)
		{
			$app = $this->container->get('applicationInstance', [false]);

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

				$config = $app->getConfig()->get('redis.'.$config, []);
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
					$instance = $this->container->multiton('Redis', $name);

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
							$server = [];
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
					$instance = $this->container->multiton('Predis\Client', $name, [$config['servers'], $config['options']]);

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
						$server = ['host' => 'localhost', 'port' => 6379];
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
					$instance = $this->container->multiton('redisent\Redis', $name, [$server['host'], $server['timeout']]);

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
