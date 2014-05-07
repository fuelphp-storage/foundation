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

use Fuel\Dependency\Container;

/**
 * Application injection factory, provides methods to allow the Application
 * class to construct or access new external objects without creating
 * dependencies
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */

class ApplicationInjectionFactory extends InjectionFactory
{
	/**
	 *
	 */
	public function createEnvironmentContainer($app, $environment)
	{
		return $this->container->multiton('environment', $app->getName(), array($environment));
	}

	/**
	 *
	 */
	public function getAutoloaderInstance()
	{
		return $this->container->resolve('autoloader');
	}

	/**
	 *
	 */
	public function getRouteFilter($app)
	{
		return $this->container->resolve('routefilter', array($app));
	}

	/**
	 *
	 */
	public function createViewmanagerInstance($name, $path)
	{
		return $this->container->multiton('viewmanager', $name, array(
			$this->container->resolve('finder', array(array(realpath(__DIR__.DS.'..'.DS.'..'.DS.'../defaults'), $path))),
			array('cache' => $path.'cache'),
		));
	}

	/**
	 *
	 */
	public function createViewParserInstance($name)
	{
		return $this->container->resolve($name);
	}

	/**
	 *
	 */
	public function registerApplication($app)
	{
		// if we don't have an active application yet, make this one active
		try
		{
			$this->container->resolve('application.main');
		}
		catch (\Fuel\Dependency\ResolveException $e)
		{
			$this->container->inject('application.main', $app);
		}
	}

	/**
	 * Check if the current request is the main request
	 *
	 * @return  bool  Whether or not this is the main request
	 *
	 * @since  2.0.0
	 */
	public function isMainRequest()
	{
		$stack = $this->container->resolve('requeststack');
		return count($stack) === 1;
	}

}
