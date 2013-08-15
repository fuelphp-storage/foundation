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
		'response', 'response.html', 'response.json', 'response.jsonp', 'response.csv', 'response.xml', 'response.redirect',
		'request', 'request.local',
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
				$config = $this->container->resolve('application.main')->getConfig();
			}

			if (is_callable(array($instance, 'setConfig')))
			{
				$instance->setConfig($config);
			}
			else
			{
				$instance->config = $config;
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
		$this->register('input', function ($dic, array $inputVars = array(), $parent = null)
		{
			return $dic->resolve('Fuel\Foundation\Input', array($inputVars, $parent));
		});

		// \Fuel\Foundation\Application
		$this->register('application', function ($dic, $appName, $appPath, $namespace, $environment)
		{
			// application path
			if (empty($appPath))
			{
				$appPath = APPSPATH.$appName;
			}
			if ( ! is_dir($appPath = realpath($appPath)))
			{
				throw new \InvalidArgumentException('The path "'.$appPath.'" does not exist for application "'.$appName.'".');
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


			return $dic->resolve('Fuel\Foundation\Application', array($appName, $appPath, $namespace, $environment));
		});

		// \Fuel\Foundation\Environment
		$this->register('environment', function ($dic, $app, $environment, $input, $config)
		{
			return $dic->resolve('Fuel\Foundation\Environment', array($app, $environment, $input, $config));
		});

		$this->registerSingleton('requeststack', function ($dic)
		{
			return $dic->resolve('Fuel\Dependency\Stack');
		});

		// \Fuel\Foundation\Request\...
		$this->register('request', function ($dic, $app, $resource = '', Array $input = array(), $type = null)
		{
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

// TODO: find the parent request, get it's input container, and assign this to $inp
			$parent = null;

			// construct an input instance for this request
			$input = $dic->resolve('input', array($input, $parent));

			// return the constructed request
			return $dic->resolve('request.'.$type, array($app, $resource, $input));
		});

		// \Fuel\Foundation\Request\Local
		$this->register('request.local', function ($dic, $app, $resource = '', $inputInstance = null)
		{
			return $dic->resolve('Fuel\Foundation\Request\Local', array($app, $resource, $inputInstance));
		});
		$this->extend('request.local', 'getApplicationInstance');
		$this->extend('request.local', 'getConfigInstance');
		$this->extend('request.local', 'getRouterInstance');
		$this->extend('request.local', 'getLogInstance');

		// \Fuel\Foundation\Response\Html
		$this->register('response', function ($dic, $content = '', $status = 200, array $headers = array())
		{
			return $dic->resolve('Fuel\Foundation\Response\Html', array($content, $status, $headers));
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
		$this->extend('response.xml', 'newFormatInstance');

		// \Fuel\Foundation\Response\Jsonp
		$this->register('response.jsonp', function ($dic, $content = '', $status = 200, array $headers = array())
		{
			return $dic->resolve('Fuel\Foundation\Response\Jsonp', array($content, $status, $headers));
		});
		$this->extend('response.jsonp', 'getRequestInstance');
		$this->extend('response.xml', 'newFormatInstance');

		// \Fuel\Foundation\Response\Csv
		$this->register('response.csv', function ($dic, $content = '', $status = 200, array $headers = array())
		{
			return $dic->resolve('Fuel\Foundation\Response\Csv', array($content, $status, $headers));
		});
		$this->extend('response.csv', 'getRequestInstance');
		$this->extend('response.xml', 'newFormatInstance');

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

		/**
		 * Service definitions for required non-Fuel classes
		 */

		// \Monolog\Logger
		$this->register('log', function ($dic, $name)
		{
			return new \Monolog\Logger($name);
		});
	}
}
