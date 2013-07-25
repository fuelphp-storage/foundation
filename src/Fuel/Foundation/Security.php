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

/**
 * Base Security class
 *
 * Container for various Security handlers.
 *
 * @package  Fuel\Foundation
 *
 * @since  1.0.0
 */
class Security
{
	/**
	 * @var  Application
	 *
	 * @since  2.0.0
	 */
	protected $app;

	/**
	 * @var  Security\Crypt
	 *
	 * @since  2.0.0
	 */
	protected $crypt;

	/**
	 * @var  Security\Csrf
	 *
	 * @since  2.0.0
	 */
	protected $csrf;

	/**
	 * @var  Security\String
	 *
	 * @since  2.0.0
	 */
	protected $string;

	/**
	 * Constructor
	 *
	 * @since  2.0.0
	 */
	public function __construct($app)
	{
		$this->app = $app;
	}

	/**
	 * Returns the App's Crypt instance
	 *
	 * @return  Crypt\Cryptable
	 *
	 * @since  2.0.0
	 */
	public function getCrypt()
	{
		! isset($this->crypt) and $this->crypt = \Dependency::resolve('Fuel\Foundation\Security\Crypt', array($this->app));
		return $this->crypt;
	}

	/**
	 * Returns the App's Csrf instance
	 *
	 * @return  Csrf\Base
	 *
	 * @since  2.0.0
	 */
	public function getCsrf()
	{
		! isset($this->csrf) and $this->csrf = \Dependency::resolve('Fuel\Foundation\Security\Csrf', array($this->app));
		return $this->csrf;
	}

	/**
	 * Returns the App's String cleaner instance
	 *
	 * @return  String\Base
	 *
	 * @since  2.0.0
	 */
	public function getStringCleaner()
	{
		! isset($this->string) and $this->string = \Dependency::resolve('Fuel\Foundation\Security\String\Htmlentities', array($this->app));
		return $this->string;
	}

	/**
	 * Separate method for cleaning the URI
	 *
	 * @param   string  $uri
	 * @param   null|bool|string|String\Base  $filter
	 * @return  string
	 *
	 * @since  1.1.0
	 */
	public function cleanUri($uri, $filter = null)
	{
		// Set default when null
		is_null($filter) and $filter = $this->app->config->get('security.uri_filter', true);

		// When true use internal security filter
		$filter === true and $filter = $this->getStringCleaner();

		// When string is passed try to fetch special filter from DiC
		is_string($filter) and $filter = \Dependency::resolve('Fuel\Foundation\Security\String\\'.$filter, array($this->app));

		// Whatever is left is either boolean false or a String Security object
		return $filter ? $filter->clean($uri) : $uri;
	}

	/**
	 * Clean a variable with the String cleaner
	 *
	 * @param   mixed  $input
	 * @return  mixed
	 *
	 * @since  1.0.0
	 */
	public function clean($input)
	{
		return $this->getStringCleaner()->clean($input);
	}

	/**
	 * Fetch the CSRF token
	 *
	 * @return string
	 *
	 * @since  1.0.0
	 */
	public function getToken()
	{
		return $this->getCsrf()->getToken();
	}

	/**
	 * Check the CSRF token
	 *
	 * @param   null|string  $token
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	public function checkToken($token = null)
	{
		return $this->getCsrf()->checkToken($token);
	}
}
