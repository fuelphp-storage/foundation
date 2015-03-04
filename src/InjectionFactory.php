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
		return $this->container->get('input', array($input, $parent));
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
		return $this->container->get('event');
	}

	/**
	 *
	 */
	public function createSessionInstance()
	{
		return $this->container->get('session');
	}

	/**
	 *
	 */
	public function createRouterInstance($component)
	{
		return $this->container->get('router', array($component));
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
		return $this->container->get('request', array($component, $uri, $input));
	}

	/**
	 *
	 */
	public function createResponseInstance($type, $content = '', $status = 200, array $headers = array())
	{
		return $this->container->get('response.'.$type, array($content, $status, $headers));
	}

	/**
	 *
	 */
	public function createDataContainer(array $contents = array())
	{
		return $this->container->get('datacontainer', $contents);
	}

	/**
	 *
	 */
	public function createCookieJar(array $cookies = array())
	{
		return $this->container->get('cookiejar', $cookies);
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
		return $this->container->get('component', array($app, $uri, $namespace, $paths, $routeable, $parent));
	}

	/**
	 *
	 */
	public function createViewmanagerInstance()
	{
		return $this->container->get('viewmanager', array(
			$this->container->get('finder', array(array(realpath(__DIR__.DS.'..'.DS.'defaults')))),
		));
	}

	/**
	 *
	 */
	public function createViewParserInstance($name)
	{
		return $this->container->get($name);
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
		$this->container->add('controller', $controller)
			->withArgument('injectionfactory')
			->withMethodCall('setApplication', ['applicationInstance'])
			->withMethodCall('setRequest', ['requestInstance']);

		return $this->container->get('controller');
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
		return $this->container->get('uri', array($uri));
	}

	/**
	 * Checks if the current request is the "main" (only one)
	 */
	public function isMainRequest()
	{
		$stack = $this->container->get('requeststack');
		return count($stack) === 1;
	}
}
