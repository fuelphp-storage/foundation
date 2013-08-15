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

	/**
	 *
	 */
	public function createConfigContainer($name, $parent = null)
	{
		if ($parent === null)
		{
			$parent = $this->container->resolve('config.global');
		}
		$config = $this->container->multiton('config', $name);
		$config->setParent($parent);

		return $config;
	}

	/**
	 *
	 */
	public function createInputContainer($parent = null)
	{
		if ($parent === null)
		{
			$parent = $this->container->resolve('input.global');
		}
		return $this->container->resolve('input', array(array(), $parent));
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
	public function createRouterInstance($name)
	{
		return $this->container->multiton('router', $name);
	}

	/**
	 *
	 */
	public function createLanguageInstance($name)
	{
		return $this->container->multiton('config', $name);
	}

	/**
	 *
	 */
	public function createRequestInstance($app, $uri, $input)
	{
		return $this->container->resolve('request', array($app, $uri, $input));
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
}
