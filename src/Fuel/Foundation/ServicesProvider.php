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

use Fuel\Dependency\ServiceProvider;

/**
 * ServicesProvider class
 *
 * Defines the services published by this namespace to the DiC
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class ServicesProvider extends ServiceProvider
{
	/**
	 * @var  array  list of service names provided by this provider
	 */
	public $provides = array('application', 'environment', 'input', 'request', 'response', 'security');

	/**
	 * Service provider definitions
	 */
	public function provide()
	{
		// \Fuel\Foundation\Application
		$this->register('application', function ($dic, $appName, $appPath, $namespace, $environment)
		{
			return new Application($appName, $appPath, $namespace, $environment);
		});

		// \Fuel\Foundation\Environment
		$this->register('environment', function ($dic, $app, $environment, $config)
		{
			return new Environment($app, $environment, $config);
		});

		// \Fuel\Foundation\Input
		$this->register('input', function ($dic, $app, array $inputVars = array(), $parent = null)
		{
			return new Input($app, $inputVars, $parent);
		});

		// \Fuel\Foundation\Request
		$this->register('request', function ($dic, $app, $resource = '', $input = null)
		{
			return new Request($app, $resource, $input);
		});

		// \Fuel\Foundation\Response
		$this->register('response', function ($dic, $app, $content = '', $status = 200, array $headers = array())
		{
			return new Response($app, $content, $status, $headers);
		});

		// \Fuel\Foundation\Security
		$this->register('security', function ($dic, $app)
		{
			return new Security($app);
		});
	}
}
