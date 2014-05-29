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

use Fuel\Dependency\Container;

/**
 * Generic injection factory, provides methods to allow classes to
 * construct or access new external objects without creating
 * dependencies
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */

class InjectionFactory
{
	/**
	 *
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	// Instance creation

	/**
	 *
	 */
	public function createConfigContainer($name, $parent = null)
	{
		$config = $this->container->multiton('config', $name);
		$config->setParent($parent);

		return $config;
	}

	/**
	 *
	 */
	public function createInputContainer($input = array(), $parent = null)
	{
		return $this->container->resolve('input', array($input, $parent));
	}

	/**
	 *
	 */
	public function createLogInstance($name)
	{
		return $this->container->multiton('log', $name, array($name));
	}

	/**
	 *
	 */
	public function createEventInstance()
	{
		return $this->container->resolve('event');
	}

	/**
	 *
	 */
	public function createSessionInstance()
	{
		return $this->container->resolve('session');
	}

	/**
	 *
	 */
	public function createRouterInstance($component)
	{
		return $this->container->resolve('router', array($component));
	}

	/**
	 *
	 */
	public function createLanguageInstance($name)
	{
		return $this->container->multiton('config', $name)->setConfigFolder('');
	}

	/**
	 *
	 */
	public function createRequestInstance($component, $uri, $input)
	{
		return $this->container->resolve('request', array($component, $uri, $input));
	}

	/**
	 *
	 */
	public function createResponseInstance($type, $content = '', $status = 200, array $headers = array())
	{
		return $this->container->resolve('response.'.$type, array($content, $status, $headers));
	}

	/**
	 *
	 */
	public function createDataContainer(array $contents = array())
	{
		return $this->container->resolve('datacontainer', $contents);
	}

	/**
	 *
	 */
	public function createCookieJar(array $cookies = array())
	{
		return $this->container->resolve('cookiejar', $cookies);
	}

	/**
	 *
	 */
	public function createEnvironmentContainer($name, $environment, $app)
	{
		return $this->container->multiton('environment', $name, array($environment, $app));
	}

	/**
	 *
	 */
	public function createComponentInstance($app, $uri, $namespace, $paths = null, $routeable = true, $parent = null)
	{
		return $this->container->resolve('component', array($app, $uri, $namespace, $paths, $routeable, $parent));
	}

	/**
	 *
	 */
	public function createViewmanagerInstance()
	{
		return $this->container->resolve('viewmanager', array(
			$this->container->resolve('finder', array(array(realpath(__DIR__.DS.'..'.DS.'defaults')))),
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
	 * create an instance of the controller
	 *
	 * @return  Controller\Base
	 *
	 * @since  2.0.0
	 */
	public function createControllerInstance($controller)
	{
		$this->container->register('controller', $controller);
		$this->container->extend('controller', 'getApplicationInstance');
		$this->container->extend('controller', 'getRequestInstance');

		return $this->container->resolve('controller');
	}

	/**
	 * create an instance of the controller
	 *
	 * @return  Controller\Base
	 *
	 * @since  2.0.0
	 */
	public function createUriInstance($uri)
	{
		return $this->container->resolve('uri', array($uri));
	}

	public function isMainRequest()
	{
		$stack = $this->container->resolve('requeststack');
		return count($stack) === 1;
	}
}
